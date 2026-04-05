<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiagnosisRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'technical_diagnosis' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'technical_diagnosis.max' => 'O diagnóstico técnico não pode ter mais de 5000 caracteres.',
        ];
    }
}
