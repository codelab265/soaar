<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Http\Resources\V1\CourseResource;
use App\Models\Course;
use App\Services\CourseRedemptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController
{
    public function __construct(
        private CourseRedemptionService $redemptionService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return CourseResource::collection(
            Course::where('is_active', true)->latest()->paginate(15)
        );
    }

    public function show(Course $course): CourseResource
    {
        return new CourseResource($course);
    }

    public function enroll(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'payment_method' => ['required', 'in:money,points,hybrid'],
            'points_to_use' => ['required_if:payment_method,hybrid', 'integer', 'min:0'],
        ]);

        $method = PaymentMethod::from($data['payment_method']);
        $user = $request->user();

        $enrollment = match ($method) {
            PaymentMethod::Money => $this->redemptionService->enrollWithMoney($user, $course),
            PaymentMethod::Points => $this->redemptionService->enrollWithPoints($user, $course),
            PaymentMethod::Hybrid => $this->redemptionService->enrollHybrid($user, $course, $data['points_to_use']),
        };

        return response()->json([
            'message' => 'Enrolled successfully.',
            'enrollment_id' => $enrollment->id,
            'payment_method' => $enrollment->payment_method->value,
            'points_used' => $enrollment->points_used,
            'amount_paid' => $enrollment->amount_paid,
        ], 201);
    }
}
