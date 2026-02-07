<?php

namespace App\Http\Requests\tenant;

use App\Dto\ServiceDto;
use App\Models\Tenant\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'base_price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'category' => ['required', 'string', Rule::in(Service::getCategories())],
            'estimated_time' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do serviço é obrigatório.',
            'name.min' => 'O nome do serviço deve ter no mínimo 3 caracteres.',
            'name.max' => 'O nome do serviço não pode ter mais de 255 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            'base_price.required' => 'O preço base é obrigatório.',
            'base_price.numeric' => 'O preço base deve ser um valor numérico.',
            'base_price.min' => 'O preço base deve ser maior que zero.',
            'base_price.max' => 'O preço base não pode ultrapassar 999999.99.',
            'category.required' => 'A categoria é obrigatória.',
            'category.in' => 'A categoria selecionada é inválida.',
            'estimated_time.integer' => 'O tempo estimado deve ser um número inteiro.',
            'estimated_time.min' => 'O tempo estimado deve ser no mínimo 1 minuto.',
            'estimated_time.max' => 'O tempo estimado não pode ultrapassar 9999 minutos.',
            'is_active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }

    public function toDto(): ServiceDto
    {
        return ServiceDto::fromArray($this->validated());
    }
}
