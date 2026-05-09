<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PayChanguCharge;
use App\Services\PayChangu\PayChanguClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayChanguChargeController extends Controller
{
    public function __construct(public PayChanguClient $payChangu) {}

    public function show(Request $request, string $chargeId): JsonResponse
    {
        $user = $request->user();

        $charge = PayChanguCharge::query()
            ->where('charge_id', $chargeId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($charge->status === 'pending') {
            $verify = match ($charge->payment_method) {
                'checkout' => $this->payChangu->verifyPayment($chargeId),
                'mobile_money' => $this->payChangu->verifyMobileMoney($chargeId),
                'bank_transfer' => $this->payChangu->verifyBankTransfer($chargeId),
                'card' => $this->payChangu->verifyCard($chargeId),
                default => null,
            };

            if (is_array($verify)) {
                $charge->update([
                    'status' => $this->mapVerifyResponseToStatus($charge->payment_method, $verify),
                    'provider_verify_response' => $verify,
                ]);
            }
        }

        return response()->json([
            'charge_id' => $charge->charge_id,
            'status' => $charge->status,
            'currency' => $charge->currency,
            'amount' => $charge->amount,
            'points_reserved' => $charge->points_reserved,
            'purpose' => $charge->purpose,
            'course_id' => $charge->course_id,
            'payment_method' => $charge->payment_method,
            'provider' => [
                'ref_id' => $charge->ref_id,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $verify
     */
    private function mapVerifyResponseToStatus(string $method, array $verify): string
    {
        return match ($method) {
            'checkout' => data_get($verify, 'data.data.status') === 'success'
                || data_get($verify, 'data.status') === 'success'
                || data_get($verify, 'status') === 'success'
                ? 'success'
                : 'pending',
            'mobile_money' => data_get($verify, 'data.status') === 'success' ? 'success' : 'pending',
            'bank_transfer' => data_get($verify, 'data.transaction.status') === 'success' ? 'success' : 'pending',
            'card' => data_get($verify, 'data.status') === 'success' ? 'success' : 'pending',
            default => 'pending',
        };
    }
}
