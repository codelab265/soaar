<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InitializePayChanguBankTransferRequest;
use App\Http\Requests\Api\V1\InitializePayChanguMobileMoneyRequest;
use App\Models\Course;
use App\Models\PayChanguCharge;
use App\Services\CourseRedemptionService;
use App\Services\PayChangu\PayChanguClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PayChanguCourseChargeController extends Controller
{
    public function __construct(
        public PayChanguClient $payChangu,
        public CourseRedemptionService $courseRedemption,
    ) {}

    public function initializeMobileMoney(
        InitializePayChanguMobileMoneyRequest $request,
        Course $course,
    ): JsonResponse {
        $user = $request->user();

        $pointsToUse = (int) ($request->validated('points_to_use') ?? 0);
        $this->guardPointsToUse($user->id, $course, $pointsToUse);

        $amountToCharge = $this->remainingMwkToCharge($course, $pointsToUse);
        $chargeId = $this->newChargeId();

        $initialize = $this->payChangu->initiateTransaction([
            'amount' => (string) $amountToCharge,
            'currency' => 'MWK',
            'tx_ref' => $chargeId,
            'email' => $user->email,
            'first_name' => $user->name,
            'callback_url' => (string) config('paychangu.checkout_callback_url', URL::to('/paychangu/callback')),
            'return_url' => (string) config('paychangu.checkout_return_url', URL::to('/paychangu/return')),
            'customization' => [
                'title' => 'Course Enrollment',
                'description' => $course->title ?? 'Course enrollment',
            ],
            'meta' => [
                'purpose' => 'course_enrollment',
                'course_id' => (string) $course->id,
                'user_id' => (string) $user->id,
                'points_reserved' => (string) $pointsToUse,
            ],
        ]);

        PayChanguCharge::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'purpose' => 'course_enrollment',
            'payment_method' => 'checkout',
            'charge_id' => $chargeId,
            'ref_id' => data_get($initialize, 'data.data.tx_ref') ?? data_get($initialize, 'data.tx_ref'),
            'currency' => 'MWK',
            'amount' => $amountToCharge,
            'points_reserved' => $pointsToUse,
            'status' => 'pending',
            'provider_initialize_response' => $initialize,
        ]);

        return response()->json([
            'status' => 'pending',
            'charge_id' => $chargeId,
            'amount' => $amountToCharge,
            'currency' => 'MWK',
            'checkout_url' => data_get($initialize, 'data.checkout_url'),
        ]);
    }

    public function initializeBankTransfer(
        InitializePayChanguBankTransferRequest $request,
        Course $course,
    ): JsonResponse {
        $user = $request->user();

        $pointsToUse = (int) ($request->validated('points_to_use') ?? 0);
        $this->guardPointsToUse($user->id, $course, $pointsToUse);

        $amountToCharge = $this->remainingMwkToCharge($course, $pointsToUse);
        $chargeId = $this->newChargeId();

        $initialize = $this->payChangu->initiateTransaction([
            'amount' => (string) $amountToCharge,
            'currency' => 'MWK',
            'tx_ref' => $chargeId,
            'email' => $user->email,
            'first_name' => $user->name,
            'callback_url' => (string) config('paychangu.checkout_callback_url', URL::to('/paychangu/callback')),
            'return_url' => (string) config('paychangu.checkout_return_url', URL::to('/paychangu/return')),
            'customization' => [
                'title' => 'Course Enrollment',
                'description' => $course->title ?? 'Course enrollment',
            ],
            'meta' => [
                'purpose' => 'course_enrollment',
                'course_id' => (string) $course->id,
                'user_id' => (string) $user->id,
                'points_reserved' => (string) $pointsToUse,
            ],
        ]);

        PayChanguCharge::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'purpose' => 'course_enrollment',
            'payment_method' => 'checkout',
            'charge_id' => $chargeId,
            'ref_id' => data_get($initialize, 'data.data.tx_ref') ?? data_get($initialize, 'data.tx_ref'),
            'currency' => 'MWK',
            'amount' => $amountToCharge,
            'points_reserved' => $pointsToUse,
            'status' => 'pending',
            'provider_initialize_response' => $initialize,
        ]);

        return response()->json([
            'status' => 'pending',
            'charge_id' => $chargeId,
            'amount' => $amountToCharge,
            'currency' => 'MWK',
            'checkout_url' => data_get($initialize, 'data.checkout_url'),
        ]);
    }

    private function newChargeId(): string
    {
        return 'SOAAR-'.Str::upper(Str::random(12));
    }

    private function remainingMwkToCharge(Course $course, int $pointsToUse): int
    {
        $pointsValueInMwk = $pointsToUse * CourseRedemptionService::POINTS_TO_MWK_RATE;

        return max(0, (int) $course->price_mwk - $pointsValueInMwk);
    }

    private function guardPointsToUse(int $userId, Course $course, int $pointsToUse): void
    {
        if ($pointsToUse < 0) {
            throw new InvalidArgumentException('points_to_use must be >= 0.');
        }

        if ($pointsToUse > (int) $course->price_points) {
            throw new InvalidArgumentException('points_to_use cannot exceed course point price.');
        }

        $user = request()->user();

        $reserved = PayChanguCharge::query()
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('points_reserved');

        $available = (int) $user->total_points - (int) $reserved;

        if ($pointsToUse > $available) {
            throw new InvalidArgumentException("Insufficient points. Requested: {$pointsToUse}, available: {$available}.");
        }
    }
}
