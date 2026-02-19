<?php

namespace App\Http\Requests\tenant;

use App\Dto\StockMovementDto;
use App\Enum\Tenant\Stock\MovementTypeEnum;
use App\Enum\Tenant\Stock\StockMovementReasonEnum;
use Illuminate\Foundation\Http\FormRequest;

class StockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'movement_type' => ['required', 'in:IN,OUT'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'in:purchase,sale,adjustment,loss,return,transfer,initial,other'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'movement_type.required' => 'O tipo de movimentação é obrigatório.',
            'movement_type.in' => 'O tipo de movimentação deve ser IN (entrada) ou OUT (saída).',
            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'quantity.min' => 'A quantidade deve ser no mínimo 1.',
            'reason.required' => 'O motivo da movimentação é obrigatório.',
            'reason.in' => 'O motivo selecionado é inválido.',
            'notes.max' => 'As observações não podem ter mais de 500 caracteres.',
        ];
    }

    public function toDto(): StockMovementDto
    {
        $data = $this->validated();
        $productId = $this->route('product_id');

        return new StockMovementDto(
            productId: $productId,
            movement: MovementTypeEnum::from($data['movement_type']),
            quantity: $data['quantity'],
            reason: StockMovementReasonEnum::from($data['reason']),
            notes: $data['notes'] ?? null,
        );
    }
}
