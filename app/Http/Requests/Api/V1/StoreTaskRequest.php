<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TaskDifficulty;
use App\Models\Goal;
use App\Models\Objective;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', Rule::enum(TaskDifficulty::class)],
            'minimum_duration' => ['sometimes', 'integer', 'min:1'],
            'points_value' => ['sometimes', 'integer', 'min:1'],
            'scheduled_date' => ['nullable', 'date'],
            'goal_id' => ['nullable', 'integer', 'exists:goals,id'],
            'objective_id' => ['nullable', 'integer', 'exists:objectives,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $goalId = $this->integer('goal_id') ?: null;
            $objectiveId = $this->integer('objective_id') ?: null;
            $routeObjective = $this->route('objective');

            if ($routeObjective instanceof Objective) {
                $objectiveId = $routeObjective->id;
                $goalId ??= $routeObjective->goal_id;
            }

            if ($objectiveId) {
                $objective = Objective::query()->select(['id', 'goal_id'])->find($objectiveId);

                if (! $objective) {
                    $validator->errors()->add('objective_id', 'The selected objective is invalid.');

                    return;
                }

                $goalId ??= $objective->goal_id;

                if ($goalId && $objective->goal_id !== $goalId) {
                    $validator->errors()->add('objective_id', 'The objective does not belong to the selected goal.');

                    return;
                }
            }

            if ($goalId) {
                $goal = Goal::query()->select(['id', 'user_id'])->find($goalId);

                if (! $goal || $goal->user_id !== $this->user()->id) {
                    $validator->errors()->add('goal_id', 'You are not allowed to create a task for this goal.');
                }
            }
        });
    }
}
