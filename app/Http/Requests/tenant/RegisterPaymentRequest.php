<?php

namespace App\Http\Requests\tenant;

use App\Dto\PaymentDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', Rule::in(['cash', 'credit_card', 'debit_card', 'pix', 'bank_transfer', 'check'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'O método de pagamento é obrigatório.',
            'payment_method.in' => 'O método de pagamento selecionado é inválido.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser numérico.',
            'amount.min' => 'O valor mínimo é R$ 0,01.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }

    public function toDto(string $serviceOrderId): PaymentDto
    {
        $validated = $this->validated();
        $validated['service_order_id'] = $serviceOrderId;

        return PaymentDto::fromArray($validated);
    }
}
