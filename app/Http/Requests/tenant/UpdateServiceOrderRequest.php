<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'observations' => ['nullable', 'string', 'max:5000'],
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis.max' => 'O diagnóstico não pode ter mais de 5000 caracteres.',
            'observations.max' => 'As observações não podem ter mais de 5000 caracteres.',
            'technician_id.exists' => 'O técnico selecionado não existe.',
            'discount.numeric' => 'O desconto deve ser um valor numérico.',
            'discount.min' => 'O desconto não pode ser negativo.',
        ];
    }
}
