<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class CancelServiceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'O motivo do cancelamento é obrigatório.',
            'reason.max' => 'O motivo não pode ter mais de 1000 caracteres.',
        ];
    }
}
