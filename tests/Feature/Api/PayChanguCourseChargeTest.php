<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\PayChanguCharge;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('paychangu.secret_key', 'sec-test');
    config()->set('paychangu.webhook_secret', 'whsec-test');
    config()->set('paychangu.base_url', 'https://api.paychangu.com');

    $this->user = User::factory()->create(['total_points' => 2000]);
    $this->course = Course::factory()->create([
        'price_mwk' => 10000,
        'price_points' => 1000,
    ]);
});

it('initializes a bank transfer charge for a course', function () {
    Http::fake([
        'api.paychangu.com/payment' => Http::response([
            'status' => 'success',
            'message' => 'Hosted payment session generated successfully.',
            'data' => [
                'event' => 'checkout.session:created',
                'checkout_url' => 'https://checkout.paychangu.com/923677185321',
                'data' => [
                    'tx_ref' => 'SOAAR-TXREF-123',
                    'currency' => 'MWK',
                    'amount' => 7500,
                    'status' => 'pending',
                ],
            ],
        ]),
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/courses/{$this->course->id}/paychangu/bank-transfer", [
            'points_to_use' => 250,
        ])
        ->assertSuccessful()
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('currency', 'MWK')
        ->assertJsonPath('amount', 7500)
        ->assertJsonStructure(['charge_id', 'checkout_url']);

    expect(PayChanguCharge::query()->count())->toBe(1);

    $charge = PayChanguCharge::query()->firstOrFail();

    expect($charge->user_id)->toBe($this->user->id)
        ->and($charge->course_id)->toBe($this->course->id)
        ->and($charge->payment_method)->toBe('checkout')
        ->and($charge->amount)->toBe(7500)
        ->and($charge->points_reserved)->toBe(250)
        ->and($charge->status)->toBe('pending');
});

it('accepts a signed webhook, verifies, and enrolls the user', function () {
    $charge = PayChanguCharge::create([
        'user_id' => $this->user->id,
        'course_id' => $this->course->id,
        'purpose' => 'course_enrollment',
        'payment_method' => 'checkout',
        'charge_id' => 'SOAAR-ABC123',
        'ref_id' => '75513659949',
        'currency' => 'MWK',
        'amount' => 7500,
        'points_reserved' => 250,
        'status' => 'pending',
    ]);

    Http::fake([
        'api.paychangu.com/verify-payment/SOAAR-ABC123' => Http::response([
            'status' => 'success',
            'message' => 'Transaction verified successfully.',
            'data' => [
                'data' => [
                    'tx_ref' => 'SOAAR-ABC123',
                    'status' => 'success',
                    'currency' => 'MWK',
                    'amount' => 7500,
                ],
            ],
        ]),
    ]);

    $payload = json_encode([
        'event_type' => 'api.charge.payment',
        'status' => 'success',
        'charge_id' => 'SOAAR-ABC123',
        'reference' => '75513659949',
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha256', $payload, 'whsec-test');

    $this->postJson('/api/paychangu/webhook', json_decode($payload, true), [
        'Signature' => $signature,
    ])->assertNoContent();

    $charge->refresh();

    expect($charge->status)->toBe('success');
    expect(CourseEnrollment::query()
        ->where('user_id', $this->user->id)
        ->where('course_id', $this->course->id)
        ->exists())->toBeTrue();

    $this->user->refresh();
    expect($this->user->total_points)->toBe(1750);
});

it('ignores duplicate webhook delivery for same payload fingerprint', function () {
    PayChanguCharge::create([
        'user_id' => $this->user->id,
        'course_id' => $this->course->id,
        'purpose' => 'course_enrollment',
        'payment_method' => 'checkout',
        'charge_id' => 'SOAAR-DUPL1',
        'ref_id' => '75513659949',
        'currency' => 'MWK',
        'amount' => 7500,
        'points_reserved' => 250,
        'status' => 'pending',
    ]);

    Http::fake([
        'api.paychangu.com/verify-payment/SOAAR-DUPL1' => Http::response([
            'status' => 'success',
            'data' => [
                'data' => [
                    'status' => 'success',
                ],
            ],
        ]),
    ]);

    $payload = json_encode([
        'event_type' => 'api.charge.payment',
        'status' => 'success',
        'charge_id' => 'SOAAR-DUPL1',
        'reference' => '75513659949',
    ], JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha256', $payload, 'whsec-test');
    $headers = ['Signature' => $signature];
    $body = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

    $this->postJson('/api/paychangu/webhook', $body, $headers)->assertNoContent();
    $this->postJson('/api/paychangu/webhook', $body, $headers)->assertNoContent();

    expect(CourseEnrollment::query()
        ->where('user_id', $this->user->id)
        ->where('course_id', $this->course->id)
        ->count())->toBe(1);

    $this->user->refresh();
    expect($this->user->total_points)->toBe(1750);
});
