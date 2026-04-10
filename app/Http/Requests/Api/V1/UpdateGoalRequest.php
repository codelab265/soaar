<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('goal')->user_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'why' => ['nullable', 'string'],
            'category' => ['sometimes', 'string', 'max:100'],
            'deadline' => ['sometimes', 'date', 'after:today'],
        ];
    }
}
