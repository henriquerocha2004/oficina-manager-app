<?php

namespace App\Http\Requests\tenant;

use App\Dto\ProductDto;
use App\Models\Tenant\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'barcode')->ignore($productId),
            ],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'category' => ['sometimes', 'required', 'string', Rule::in(Product::getCategories())],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'unit' => ['sometimes', 'required', 'string', Rule::in(Product::getUnits())],
            'unit_price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999999.99'],
            'suggested_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 2000 caracteres.',
            'sku.unique' => 'Este SKU já está cadastrado.',
            'sku.max' => 'O SKU não pode ter mais de 50 caracteres.',
            'barcode.unique' => 'Este código de barras já está cadastrado.',
            'barcode.max' => 'O código de barras não pode ter mais de 50 caracteres.',
            'manufacturer.max' => 'O fabricante não pode ter mais de 100 caracteres.',
            'category.required' => 'A categoria é obrigatória.',
            'category.in' => 'A categoria selecionada é inválida.',
            'min_stock_level.integer' => 'O estoque mínimo deve ser um número inteiro.',
            'min_stock_level.min' => 'O estoque mínimo não pode ser negativo.',
            'unit.required' => 'A unidade é obrigatória.',
            'unit.in' => 'A unidade selecionada é inválida.',
            'unit_price.required' => 'O preço unitário é obrigatório.',
            'unit_price.numeric' => 'O preço unitário deve ser um número.',
            'unit_price.min' => 'O preço unitário não pode ser negativo.',
            'unit_price.max' => 'O preço unitário não pode exceder 9999999.99.',
            'suggested_price.numeric' => 'O preço sugerido deve ser um número.',
            'suggested_price.min' => 'O preço sugerido não pode ser negativo.',
            'suggested_price.max' => 'O preço sugerido não pode exceder 9999999.99.',
            'is_active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }

    public function toDto(): ProductDto
    {
        return ProductDto::fromArray($this->validated());
    }
}
