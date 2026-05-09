<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\PaymentMethod;
use App\Enums\SubscriptionTier;
use App\Http\Controllers\Controller;
use App\Models\PayChanguCharge;
use App\Services\CourseRedemptionService;
use App\Services\PayChangu\PayChanguClient;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayChanguWebhookController extends Controller
{
    public function __construct(
        public PayChanguClient $payChangu,
        public CourseRedemptionService $courseRedemption,
        public SubscriptionService $subscriptions,
    ) {}

    public function __invoke(Request $request): Response
    {
        $webhookSecret = config('paychangu.webhook_secret');

        if (! is_string($webhookSecret) || $webhookSecret === '') {
            throw new RuntimeException('PAYCHANGU_WEBHOOK_SECRET is not configured.');
        }

        $payload = $request->getContent();
        $signature = (string) $request->header('Signature', '');
        $fingerprint = hash('sha256', $payload);

        $computed = hash_hmac('sha256', $payload, $webhookSecret);

        if (! hash_equals($computed, $signature)) {
            Log::warning('PayChangu webhook signature mismatch.');

            return response()->noContent(400);
        }

        /** @var array<string, mixed>|null $json */
        $json = json_decode($payload, true);

        if (! is_array($json)) {
            return response()->noContent(400);
        }

        $chargeId = data_get($json, 'charge_id');

        if (! is_string($chargeId) || $chargeId === '') {
            return response()->noContent(200);
        }

        $charge = PayChanguCharge::query()->where('charge_id', $chargeId)->first();

        if (! $charge) {
            Log::info('PayChangu webhook received for unknown charge_id.', ['charge_id' => $chargeId]);

            return response()->noContent(200);
        }

        if ($charge->last_webhook_fingerprint === $fingerprint) {
            return response()->noContent();
        }

        $charge->update([
            'provider_webhook_payload' => $json,
            'last_webhook_fingerprint' => $fingerprint,
        ]);

        $verify = match ($charge->payment_method) {
            'checkout' => $this->payChangu->verifyPayment($chargeId),
            'mobile_money' => $this->payChangu->verifyMobileMoney($chargeId),
            'bank_transfer' => $this->payChangu->verifyBankTransfer($chargeId),
            'card' => $this->payChangu->verifyCard($chargeId),
            default => null,
        };

        if (! is_array($verify)) {
            return response()->noContent(200);
        }

        $previousStatus = $charge->status;
        $newStatus = $this->mapVerifyResponseToStatus($charge->payment_method, $verify);

        $charge->update([
            'status' => $newStatus,
            'provider_verify_response' => $verify,
        ]);

        $isFirstSuccessTransition = $previousStatus !== 'success' && $newStatus === 'success';

        if ($isFirstSuccessTransition && $charge->purpose === 'course_enrollment' && $charge->course) {
            $paymentMethod = $charge->points_reserved > 0
                ? PaymentMethod::Hybrid
                : PaymentMethod::Money;

            $this->courseRedemption->finalizeExternalPaymentEnrollment(
                user: $charge->user,
                course: $charge->course,
                paymentMethod: $paymentMethod,
                pointsUsed: $charge->points_reserved,
                amountPaid: $charge->amount,
            );
        }

        if ($isFirstSuccessTransition && $charge->purpose === 'subscription_premium') {
            $this->subscriptions->subscribe($charge->user, SubscriptionTier::Premium);
        }

        return response()->noContent();
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
