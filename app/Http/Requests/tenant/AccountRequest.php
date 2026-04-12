<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome e obrigatorio.',
            'name.max' => 'O nome nao pode ter mais de 255 caracteres.',
            'password.min' => 'A senha deve ter no minimo 8 caracteres.',
            'password.confirmed' => 'A confirmacao de senha nao confere.',
            'avatar.file' => 'O avatar enviado e invalido.',
            'avatar.image' => 'O avatar deve ser uma imagem.',
            'avatar.mimes' => 'O avatar deve ser nos formatos: JPEG, JPG, PNG ou WEBP.',
            'avatar.max' => 'O avatar nao pode ser maior que 10MB.',
            'remove_avatar.boolean' => 'O campo remover avatar deve ser verdadeiro ou falso.',
        ];
    }
}
