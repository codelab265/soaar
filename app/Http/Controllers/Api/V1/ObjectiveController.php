<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreObjectiveRequest;
use App\Http\Resources\V1\ObjectiveResource;
use App\Models\Goal;
use App\Models\Objective;
use App\Services\ObjectiveCompletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ObjectiveController
{
    public function __construct(
        private ObjectiveCompletionService $completionService,
    ) {}

    public function index(Request $request, Goal $goal): AnonymousResourceCollection
    {
        abort_unless($goal->user_id === $request->user()->id, 403);

        return ObjectiveResource::collection(
            $goal->objectives()->withCount('tasks')->orderBy('priority')->get()
        );
    }

    public function store(StoreObjectiveRequest $request, Goal $goal): JsonResponse
    {
        $objective = $goal->objectives()->create($request->validated());

        return (new ObjectiveResource($objective))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Objective $objective): ObjectiveResource
    {
        abort_unless($objective->goal->user_id === $request->user()->id, 403);

        $objective->update($request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:10'],
        ]));

        return new ObjectiveResource($objective->fresh());
    }

    public function destroy(Request $request, Objective $objective): JsonResponse
    {
        abort_unless($objective->goal->user_id === $request->user()->id, 403);

        $objective->delete();

        return response()->json(['message' => 'Objective deleted.']);
    }

    public function complete(Request $request, Objective $objective): JsonResponse
    {
        abort_unless($objective->goal->user_id === $request->user()->id, 403);

        $result = $this->completionService->completeObjective($objective);

        return response()->json([
            'objective' => new ObjectiveResource($result['objective']),
            'points_awarded' => $result['points_awarded'],
        ]);
    }
}
