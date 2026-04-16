<?php

namespace App\Http\Requests\admin;

use App\Dto\Admin\TenantUpdateDto;
use App\Dto\TenantCreateDto;
use App\Enum\Admin\TenantStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'domain' => ['required', 'string', 'max:25'],
            'document' => ['required', 'string', 'max:20'],
        ];

        $rules['status'] = ['nullable', Rule::enum(TenantStatus::class)];
        $rules['client_id'] = ['nullable', 'string', 'max:26'];
        $rules['trial_until'] = ['nullable', 'date', 'required_if:status,trial'];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['status'] = ['required', Rule::enum(TenantStatus::class)];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço de email válido.',
            'domain.required' => 'O domínio é obrigatório.',
            'domain.max' => 'O domínio não pode ter mais de 25 caracteres.',
            'document.required' => 'O documento é obrigatório.',
            'status.required' => 'O status é obrigatório.',
            'status.enum' => 'O status deve ser active, inactive ou trial.',
            'trial_until.required_if' => 'A data de expiração do trial é obrigatória quando o status é trial.',
        ];
    }

    public function toCreateDto(): TenantCreateDto
    {
        return new TenantCreateDto(
            name: $this->validated('name'),
            document: $this->validated('document'),
            email: $this->validated('email'),
            domain: $this->validated('domain'),
            status: $this->validated('status') ?? 'active',
            trial_until: $this->validated('trial_until'),
            client_id: $this->validated('client_id'),
        );
    }

    public function toUpdateDto(): TenantUpdateDto
    {
        return TenantUpdateDto::fromArray($this->validated());
    }
}
