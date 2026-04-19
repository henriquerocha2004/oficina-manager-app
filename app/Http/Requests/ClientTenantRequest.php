<?php

namespace App\Http\Requests;

use App\Dto\Admin\ClientDto;
use App\Enum\Admin\TenantStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientTenantRequest extends FormRequest
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
            'domain' => ['required', 'string', 'max:25'],
            'status' => ['required', Rule::enum(TenantStatus::class)],
            'trial_until' => ['nullable', 'date', 'required_if:status,trial'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço de email válido.',
            'document.required' => 'O CPF/CNPJ é obrigatório.',
            'zip_code.regex' => 'O CEP deve conter 8 dígitos numéricos.',
            'domain.required' => 'O domínio é obrigatório.',
            'domain.max' => 'O domínio não pode ter mais de 25 caracteres.',
            'status.required' => 'O status é obrigatório.',
            'status.enum' => 'O status deve ser active, inactive ou trial.',
            'trial_until.required_if' => 'A data de expiração do trial é obrigatória quando o status é trial.',
        ];
    }

    public function toClientDto(): ClientDto
    {
        return ClientDto::fromArray($this->validated());
    }

    public function tenantDomain(): string
    {
        return $this->validated('domain');
    }

    public function tenantStatus(): string
    {
        return $this->validated('status');
    }

    public function tenantTrialUntil(): ?string
    {
        return $this->validated('trial_until');
    }
}
