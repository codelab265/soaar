<?php

use App\Models\PayChanguCharge;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('paychangu.secret_key', 'sec-test');
    config()->set('paychangu.webhook_secret', 'whsec-test');
    config()->set('paychangu.base_url', 'https://api.paychangu.com');

    $this->user = User::factory()->create();
});

it('blocks premium endpoints when user is not premium', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/leaderboard')
        ->assertForbidden()
        ->assertJsonPath('message', 'Premium subscription required.');

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/analytics/completion-rate')
        ->assertForbidden();
});

it('unlocks premium endpoints after paychangu subscription payment is verified', function () {
    PayChanguCharge::create([
        'user_id' => $this->user->id,
        'course_id' => null,
        'purpose' => 'subscription_premium',
        'payment_method' => 'bank_transfer',
        'charge_id' => 'SOAAR-SUB-XYZ123',
        'ref_id' => '75513659949',
        'currency' => 'MWK',
        'amount' => 5000,
        'points_reserved' => 0,
        'status' => 'pending',
    ]);

    Http::fake([
        'api.paychangu.com/direct-charge/transactions/SOAAR-SUB-XYZ123/details' => Http::response([
            'status' => 'success',
            'message' => 'Transaction retrieved successfully.',
            'data' => [
                'transaction' => [
                    'charge_id' => 'SOAAR-SUB-XYZ123',
                    'status' => 'success',
                ],
            ],
        ]),
    ]);

    $payload = json_encode([
        'event_type' => 'api.charge.payment',
        'status' => 'success',
        'charge_id' => 'SOAAR-SUB-XYZ123',
        'reference' => '75513659949',
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha256', $payload, 'whsec-test');

    $this->postJson('/api/paychangu/webhook', json_decode($payload, true), [
        'Signature' => $signature,
    ])->assertNoContent();

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/leaderboard')
        ->assertSuccessful();
});
