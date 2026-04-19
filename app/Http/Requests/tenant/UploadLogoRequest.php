<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class UploadLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required' => 'Nenhuma imagem foi enviada.',
            'logo.file' => 'O arquivo enviado e invalido.',
            'logo.image' => 'O arquivo deve ser uma imagem.',
            'logo.mimes' => 'A logomarca deve ser nos formatos: JPEG, JPG, PNG ou WEBP.',
            'logo.max' => 'A logomarca nao pode ser maior que 5MB.',
        ];
    }
}
