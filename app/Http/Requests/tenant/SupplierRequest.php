<?php

namespace App\Http\Requests\tenant;

use App\Dto\SupplierDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierId = $this->route('id');

        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'document_number' => [
                'required',
                'string',
                'cnpj',
                Rule::unique('supplier', 'document_number')
                    ->ignore($supplierId)
                    ->whereNull('deleted_at'),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'min:10', 'max:20'],
            'mobile' => ['nullable', 'string', 'min:10', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'payment_term_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'trade_name.max' => 'O nome fantasia não pode ter mais de 255 caracteres.',
            'document_number.required' => 'O CNPJ é obrigatório.',
            'document_number.cnpj' => 'O CNPJ informado não é válido.',
            'document_number.unique' => 'Este CNPJ já está cadastrado.',
            'email.email' => 'O email deve ser um endereço de email válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'phone.min' => 'O telefone deve ter no mínimo 10 caracteres.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'mobile.min' => 'O celular deve ter no mínimo 10 caracteres.',
            'mobile.max' => 'O celular não pode ter mais de 20 caracteres.',
            'website.url' => 'O website deve ser uma URL válida.',
            'website.max' => 'O website não pode ter mais de 255 caracteres.',
            'street.max' => 'O logradouro não pode ter mais de 255 caracteres.',
            'number.max' => 'O número não pode ter mais de 20 caracteres.',
            'complement.max' => 'O complemento não pode ter mais de 100 caracteres.',
            'neighborhood.max' => 'O bairro não pode ter mais de 100 caracteres.',
            'city.max' => 'A cidade não pode ter mais de 100 caracteres.',
            'state.size' => 'O estado deve ter exatamente 2 caracteres (UF).',
            'zip_code.max' => 'O CEP não pode ter mais de 10 caracteres.',
            'contact_person.max' => 'O nome do contato não pode ter mais de 255 caracteres.',
            'payment_term_days.integer' => 'O prazo de pagamento deve ser um número inteiro.',
            'payment_term_days.min' => 'O prazo de pagamento deve ser no mínimo 0 dias.',
            'payment_term_days.max' => 'O prazo de pagamento não pode ser maior que 365 dias.',
            'notes.max' => 'As observações não podem ter mais de 2000 caracteres.',
            'is_active.boolean' => 'O status ativo deve ser verdadeiro ou falso.',
        ];
    }

    public function toDto(): SupplierDto
    {
        return SupplierDto::fromArray($this->validated());
    }
}
