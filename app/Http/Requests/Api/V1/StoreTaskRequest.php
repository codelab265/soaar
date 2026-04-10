<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TaskDifficulty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('objective')->goal->user_id;
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
        ];
    }
}
