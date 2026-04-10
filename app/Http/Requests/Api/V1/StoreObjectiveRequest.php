<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreObjectiveRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:10'],
        ];
    }
}
