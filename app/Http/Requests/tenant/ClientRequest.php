<?php

namespace App\Http\Requests\tenant;

use App\Dto\ClientDto;
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
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
            ],
            'document_number' => [
                'required',
                'string',
                'max:20',
                'cpf_ou_cnpj',
            ],
            'phone' => [
                'required',
                'string',
                'celular',
            ],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => [
                'nullable',
                'string',
                'regex:/^[0-9]{5}-[0-9]{3}$/',
            ],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço de email válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'document_number.required' => 'O CPF/CNPJ é obrigatório.',
            'document_number.unique' => 'Este CPF/CNPJ já está cadastrado.',
            'phone.required' => 'O telefone é obrigatório.',
            'phone.regex' => 'O telefone deve estar no formato (XX) XXXXX-XXXX ou (XX) XXXX-XXXX.',
            'state.max' => 'O estado deve ter no máximo 2 caracteres (UF).',
            'zip_code.regex' => 'O CEP deve estar no formato XXXXX-XXX.',
            'observations.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }

    public function toDto(): ClientDto
    {
        return ClientDto::fromArray($this->validated());
    }
}
