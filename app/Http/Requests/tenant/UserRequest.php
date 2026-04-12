<?php

namespace App\Http\Requests\tenant;

use App\Dto\UserDto;
use App\Enum\Tenant\User\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        $passwordRules = $this->isMethod('post')
            ? ['required', 'string', 'min:8', 'confirmed']
            : ['nullable', 'string', 'min:8', 'confirmed'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($userId)
                    ->whereNull('deleted_at'),
            ],
            'role' => ['required', Rule::in(array_column(UserRoleEnum::cases(), 'value'))],
            'password' => $passwordRules,
            'is_active' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome e obrigatorio.',
            'name.max' => 'O nome nao pode ter mais de 255 caracteres.',
            'email.required' => 'O email e obrigatorio.',
            'email.email' => 'O email deve ser um endereco valido.',
            'email.max' => 'O email nao pode ter mais de 255 caracteres.',
            'email.unique' => 'Este email ja esta cadastrado.',
            'role.required' => 'O perfil e obrigatorio.',
            'role.in' => 'O perfil informado e invalido.',
            'password.required' => 'A senha e obrigatoria.',
            'password.min' => 'A senha deve ter no minimo 8 caracteres.',
            'password.confirmed' => 'A confirmacao de senha nao confere.',
            'is_active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
            'avatar.file' => 'O avatar enviado e invalido.',
            'avatar.image' => 'O avatar deve ser uma imagem.',
            'avatar.mimes' => 'O avatar deve ser nos formatos: JPEG, JPG, PNG ou WEBP.',
            'avatar.max' => 'O avatar nao pode ser maior que 10MB.',
            'remove_avatar.boolean' => 'O campo remover avatar deve ser verdadeiro ou falso.',
        ];
    }

    public function toDto(): UserDto
    {
        $validated = $this->validated();

        return UserDto::fromArray([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $validated['password'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);
    }
}
