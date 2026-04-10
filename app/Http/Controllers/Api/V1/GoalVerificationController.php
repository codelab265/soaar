<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Goal;
use App\Services\GoalVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalVerificationController
{
    public function __construct(
        private GoalVerificationService $verificationService,
    ) {}

    public function approve(Request $request, Goal $goal): JsonResponse
    {
        abort_unless($goal->accountability_partner_id === $request->user()->id, 403);

        $result = $this->verificationService->approveGoal($goal);

        return response()->json([
            'message' => 'Goal approved successfully.',
            'total_points' => $result['total_points'],
        ]);
    }

    public function reject(Request $request, Goal $goal): JsonResponse
    {
        abort_unless($goal->accountability_partner_id === $request->user()->id, 403);

        $this->verificationService->rejectGoal($goal);

        return response()->json(['message' => 'Goal rejected.']);
    }
}
