<?php

namespace App\Http\Requests\admin;

use App\Dto\Admin\ClientDto;
use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'document' => ['required', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'regex:/^[0-9]{8}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço de email válido.',
            'document.required' => 'O CPF/CNPJ é obrigatório.',
            'state.max' => 'O estado deve ter no máximo 2 caracteres (UF).',
            'zip_code.regex' => 'O CEP deve conter 8 dígitos numéricos.',
        ];
    }

    public function toDto(): ClientDto
    {
        return ClientDto::fromArray($this->validated());
    }
}
