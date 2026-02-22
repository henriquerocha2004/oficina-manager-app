<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'discount.numeric' => 'O desconto deve ser um valor numérico.',
            'discount.min' => 'O desconto não pode ser negativo.',
        ];
    }
}
