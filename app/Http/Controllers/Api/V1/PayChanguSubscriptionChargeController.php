<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InitializePayChanguBankTransferRequest;
use App\Http\Requests\Api\V1\InitializePayChanguMobileMoneyRequest;
use App\Models\PayChanguCharge;
use App\Services\PayChangu\PayChanguClient;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PayChanguSubscriptionChargeController extends Controller
{
    public function __construct(
        public PayChanguClient $payChangu,
        public SubscriptionService $subscriptions,
    ) {}

    public function initializeMobileMoney(InitializePayChanguMobileMoneyRequest $request): JsonResponse
    {
        $user = $request->user();
        $amount = (int) (SubscriptionService::TIER_PRICES['premium'] ?? 5000);
        $chargeId = $this->newChargeId();

        $initialize = $this->payChangu->initiateTransaction([
            'amount' => (string) $amount,
            'currency' => 'MWK',
            'tx_ref' => $chargeId,
            'email' => $user->email,
            'first_name' => $user->name,
            'callback_url' => (string) config('paychangu.checkout_callback_url', URL::to('/paychangu/callback')),
            'return_url' => (string) config('paychangu.checkout_return_url', URL::to('/paychangu/return')),
            'customization' => [
                'title' => 'Premium Subscription',
                'description' => 'Premium subscription payment',
            ],
            'meta' => [
                'purpose' => 'subscription_premium',
                'user_id' => (string) $user->id,
            ],
        ]);

        PayChanguCharge::create([
            'user_id' => $user->id,
            'course_id' => null,
            'purpose' => 'subscription_premium',
            'payment_method' => 'checkout',
            'charge_id' => $chargeId,
            'ref_id' => data_get($initialize, 'data.data.tx_ref') ?? data_get($initialize, 'data.tx_ref'),
            'currency' => 'MWK',
            'amount' => $amount,
            'points_reserved' => 0,
            'status' => 'pending',
            'provider_initialize_response' => $initialize,
        ]);

        return response()->json([
            'status' => 'pending',
            'charge_id' => $chargeId,
            'amount' => $amount,
            'currency' => 'MWK',
            'checkout_url' => data_get($initialize, 'data.checkout_url'),
        ]);
    }

    public function initializeBankTransfer(InitializePayChanguBankTransferRequest $request): JsonResponse
    {
        $user = $request->user();
        $amount = (int) (SubscriptionService::TIER_PRICES['premium'] ?? 5000);
        $chargeId = $this->newChargeId();

        $initialize = $this->payChangu->initiateTransaction([
            'amount' => (string) $amount,
            'currency' => 'MWK',
            'tx_ref' => $chargeId,
            'email' => $user->email,
            'first_name' => $user->name,
            'callback_url' => (string) config('paychangu.checkout_callback_url', URL::to('/paychangu/callback')),
            'return_url' => (string) config('paychangu.checkout_return_url', URL::to('/paychangu/return')),
            'customization' => [
                'title' => 'Premium Subscription',
                'description' => 'Premium subscription payment',
            ],
            'meta' => [
                'purpose' => 'subscription_premium',
                'user_id' => (string) $user->id,
            ],
        ]);

        PayChanguCharge::create([
            'user_id' => $user->id,
            'course_id' => null,
            'purpose' => 'subscription_premium',
            'payment_method' => 'checkout',
            'charge_id' => $chargeId,
            'ref_id' => data_get($initialize, 'data.data.tx_ref') ?? data_get($initialize, 'data.tx_ref'),
            'currency' => 'MWK',
            'amount' => $amount,
            'points_reserved' => 0,
            'status' => 'pending',
            'provider_initialize_response' => $initialize,
        ]);

        return response()->json([
            'status' => 'pending',
            'charge_id' => $chargeId,
            'amount' => $amount,
            'currency' => 'MWK',
            'checkout_url' => data_get($initialize, 'data.checkout_url'),
        ]);
    }

    private function newChargeId(): string
    {
        return 'SOAAR-SUB-'.Str::upper(Str::random(10));
    }
}
