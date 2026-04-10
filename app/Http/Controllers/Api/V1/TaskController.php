<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TaskDifficulty;
use App\Http\Requests\Api\V1\CompleteTaskRequest;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Objective;
use App\Models\Task;
use App\Services\TaskCompletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController
{
    public function __construct(
        private TaskCompletionService $completionService,
    ) {}

    public function index(Request $request, Objective $objective): AnonymousResourceCollection
    {
        abort_unless($objective->goal->user_id === $request->user()->id, 403);

        return TaskResource::collection(
            $objective->tasks()->orderBy('scheduled_date')->get()
        );
    }

    public function store(StoreTaskRequest $request, Objective $objective): JsonResponse
    {
        $data = $request->validated();

        if (! isset($data['points_value']) && isset($data['difficulty'])) {
            $data['points_value'] = TaskDifficulty::from($data['difficulty'])->points();
        }

        $task = $objective->tasks()->create(array_merge($data, [
            'repetition_count' => 0,
            'repetition_decay' => 1.00,
        ]));

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Task $task): TaskResource
    {
        abort_unless($task->objective->goal->user_id === $request->user()->id, 403);

        $task->update($request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'scheduled_date' => ['nullable', 'date'],
        ]));

        return new TaskResource($task->fresh());
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        abort_unless($task->objective->goal->user_id === $request->user()->id, 403);

        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }

    public function complete(CompleteTaskRequest $request, Task $task): JsonResponse
    {
        $result = $this->completionService->completeTask(
            $task,
            $request->validated('duration_minutes')
        );

        return response()->json([
            'task' => new TaskResource($result['task']),
            'points_awarded' => $result['points_awarded'],
            'reason' => $result['reason'],
        ]);
    }
}
