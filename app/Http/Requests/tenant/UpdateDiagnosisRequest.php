<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiagnosisRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'diagnosis' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis.max' => 'O diagnóstico não pode ter mais de 5000 caracteres.',
        ];
    }
}
