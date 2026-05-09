<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InitializePayChanguMobileMoneyRequest extends FormRequest
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
            'mobile' => ['sometimes', 'string'],
            'mobile_money_operator_ref_id' => ['sometimes', 'string'],
            'points_to_use' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
