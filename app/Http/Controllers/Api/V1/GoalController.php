<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreGoalRequest;
use App\Http\Requests\Api\V1\UpdateGoalRequest;
use App\Http\Resources\V1\GoalResource;
use App\Models\Goal;
use App\Services\GoalService;
use App\Services\GoalVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GoalController
{
    public function __construct(
        private GoalService $goalService,
        private GoalVerificationService $verificationService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $goals = $request->user()->goals()
            ->withCount('objectives')
            ->latest()
            ->paginate(15);

        return GoalResource::collection($goals);
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        $goal = $request->user()->goals()->create($request->validated());

        return (new GoalResource($goal))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Goal $goal): GoalResource
    {
        abort_unless($goal->user_id === $request->user()->id, 403);

        $goal->loadCount('objectives');
        $goal->load('accountabilityPartner');

        return new GoalResource($goal);
    }

    public function update(UpdateGoalRequest $request, Goal $goal): GoalResource
    {
        $goal->update($request->validated());

        return new GoalResource($goal->fresh());
    }

    public function destroy(Request $request, Goal $goal): JsonResponse
    {
        abort_unless($goal->user_id === $request->user()->id, 403);

        if (! $this->goalService->canDeleteGoal($goal)) {
            return response()->json([
                'message' => 'Goal deletion is on cooldown. Please wait 24 hours between deletions.',
            ], 422);
        }

        $goal->delete();

        return response()->json(['message' => 'Goal deleted.']);
    }

    public function cancel(Request $request, Goal $goal): GoalResource
    {
        abort_unless($goal->user_id === $request->user()->id, 403);

        $goal = $this->goalService->cancelGoal($goal);

        return new GoalResource($goal);
    }

    public function submitVerification(Request $request, Goal $goal): GoalResource
    {
        abort_unless($goal->user_id === $request->user()->id, 403);

        $goal = $this->verificationService->submitForVerification($goal);

        return new GoalResource($goal);
    }
}
