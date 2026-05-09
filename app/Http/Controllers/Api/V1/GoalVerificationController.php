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

    public function requestProof(Request $request, Goal $goal): JsonResponse
    {
        abort_unless($goal->accountability_partner_id === $request->user()->id, 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $updatedGoal = $this->verificationService->requestProof($goal, $validated['message']);

        return response()->json([
            'message' => 'Proof requested successfully.',
            'goal' => $updatedGoal,
        ]);
    }

    public function submitProof(Request $request, Goal $goal): JsonResponse
    {
        abort_unless($goal->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'submission' => ['required', 'string', 'max:2000'],
        ]);

        $updatedGoal = $this->verificationService->submitProof($goal, $validated['submission']);

        return response()->json([
            'message' => 'Proof submitted successfully.',
            'goal' => $updatedGoal,
        ]);
    }
}
