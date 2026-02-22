<?php

namespace App\Http\Requests\tenant;

use App\Dto\ServiceOrderItemDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddServiceOrderItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['service', 'part'])],
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'service_id' => ['nullable', 'string', 'exists:services,id'],
            'product_id' => ['nullable', 'string', 'exists:products,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'O tipo é obrigatório.',
            'type.in' => 'O tipo deve ser "service" ou "part".',
            'description.required' => 'A descrição é obrigatória.',
            'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'quantity.min' => 'A quantidade mínima é 1.',
            'unit_price.required' => 'O preço unitário é obrigatório.',
            'unit_price.numeric' => 'O preço unitário deve ser um valor numérico.',
            'unit_price.min' => 'O preço unitário não pode ser negativo.',
            'service_id.exists' => 'O serviço selecionado não existe.',
            'product_id.exists' => 'O produto selecionado não existe.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }

    public function toDto(): ServiceOrderItemDto
    {
        return ServiceOrderItemDto::fromArray($this->validated());
    }
}
