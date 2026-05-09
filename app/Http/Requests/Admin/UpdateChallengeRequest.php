<?php

namespace App\Http\Requests\Admin;

use App\Enums\ChallengeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChallengeRequest extends FormRequest
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
        $statuses = array_map(
            fn (ChallengeStatus $status): string => $status->value,
            ChallengeStatus::cases(),
        );

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'reward_points' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', 'string', Rule::in($statuses)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
