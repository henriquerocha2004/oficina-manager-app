<?php

namespace App\Http\Requests\tenant;

use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRefundRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount'  => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', Rule::in(array_column(PaymentMethodEnum::cases(), 'value'))],
            'notes' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'O valor do estorno é obrigatório.',
            'amount.numeric' => 'O valor do estorno deve ser numérico.',
            'amount.min' => 'O valor mínimo do estorno é R$ 0,01.',
            'payment_method.required' => 'A forma de devolução é obrigatória.',
            'payment_method.in'       => 'A forma de devolução selecionada é inválida.',
            'notes.required'          => 'O motivo do estorno é obrigatório.',
            'notes.min'               => 'O motivo deve ter pelo menos 3 caracteres.',
        ];
    }
}
