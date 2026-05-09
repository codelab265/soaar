<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('paychangu.secret_key', 'sec-test');
    config()->set('paychangu.base_url', 'https://api.paychangu.com');

    $this->user = User::factory()->create();
});

it('lists paychangu mobile money operators', function () {
    Http::fake([
        'api.paychangu.com/mobile-money' => Http::response([
            'status' => 'success',
            'message' => 'Supported mobile money operators retrieved successfully.',
            'data' => [
                [
                    'id' => 1,
                    'name' => 'TNM Mpamba',
                    'ref_id' => '27494cb5-ba9e-437f-a114-4e7a7686bcca',
                    'short_code' => 'tnm',
                    'supported_country' => [
                        'name' => 'Malawi',
                        'currency' => 'MWK',
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'Airtel Money',
                    'ref_id' => '20be6c20-adeb-4b5b-a7ba-0769820df4fb',
                    'short_code' => 'airtel',
                    'supported_country' => [
                        'name' => 'Malawi',
                        'currency' => 'MWK',
                    ],
                ],
            ],
        ]),
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/paychangu/mobile-money/operators')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'TNM Mpamba')
        ->assertJsonPath('data.0.ref_id', '27494cb5-ba9e-437f-a114-4e7a7686bcca')
        ->assertJsonPath('data.1.name', 'Airtel Money')
        ->assertJsonPath('data.1.ref_id', '20be6c20-adeb-4b5b-a7ba-0769820df4fb');
});
