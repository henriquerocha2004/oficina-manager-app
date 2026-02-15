<?php

namespace App\Http\Requests\tenant;

use App\Dto\ProductSupplierDto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_sku' => ['sometimes', 'nullable', 'string', 'max:50'],
            'cost_price' => ['sometimes', 'numeric', 'min:0', 'max:9999999.99'],
            'lead_time_days' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'min_order_quantity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'is_preferred' => ['sometimes', 'nullable', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_sku.max' => 'O SKU do fornecedor não pode ter mais de 50 caracteres.',
            'cost_price.required' => 'O preço de custo é obrigatório.',
            'cost_price.numeric' => 'O preço de custo deve ser um número.',
            'cost_price.min' => 'O preço de custo não pode ser negativo.',
            'cost_price.max' => 'O preço de custo não pode exceder 9999999.99.',
            'lead_time_days.integer' => 'O prazo de entrega deve ser um número inteiro.',
            'lead_time_days.min' => 'O prazo de entrega deve ser no mínimo 1 dia.',
            'min_order_quantity.integer' => 'A quantidade mínima de pedido deve ser um número inteiro.',
            'min_order_quantity.min' => 'A quantidade mínima de pedido deve ser no mínimo 1.',
            'is_preferred.boolean' => 'O campo preferencial deve ser verdadeiro ou falso.',
            'notes.max' => 'As observações não podem ter mais de 2000 caracteres.',
        ];
    }

    public function toDto(): ProductSupplierDto
    {
        $data = $this->validated();
        $data['supplier_id'] = $this->route('supplierId');

        return ProductSupplierDto::fromArray($data);
    }
}
