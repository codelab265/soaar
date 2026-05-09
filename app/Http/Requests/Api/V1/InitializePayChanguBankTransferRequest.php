<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InitializePayChanguBankTransferRequest extends FormRequest
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
            'points_to_use' => ['sometimes', 'integer', 'min:0'],
            'create_permanent_account' => ['sometimes', 'boolean'],
        ];
    }
}
