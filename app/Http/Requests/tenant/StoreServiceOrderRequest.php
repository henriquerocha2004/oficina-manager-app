<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Cliente: ID OU dados completos (mutuamente exclusivos)
            'client_id' => [
                'required_without:new_client',
                'nullable',
                'string',
                'exists:client,id',
            ],
            'new_client' => ['required_without:client_id', 'array'],
            'new_client.name' => ['required_with:new_client', 'string', 'max:255'],
            'new_client.email' => ['required_with:new_client', 'email', 'max:255'],
            'new_client.document_number' => ['required_with:new_client', 'string', 'cpf_ou_cnpj'],
            'new_client.phone' => ['required_with:new_client', 'string'],
            'new_client.street' => ['nullable', 'string', 'max:255'],
            'new_client.city' => ['nullable', 'string', 'max:255'],
            'new_client.state' => ['nullable', 'string', 'size:2'],
            'new_client.zip_code' => ['nullable', 'string'],
            'new_client.observations' => ['nullable', 'string', 'max:5000'],

            // Veículo: ID OU dados completos (mutuamente exclusivos)
            'vehicle_id' => [
                'required_without:new_vehicle',
                'nullable',
                'string',
                'exists:vehicle,id',
            ],
            'new_vehicle' => ['required_without:vehicle_id', 'array'],
            'new_vehicle.license_plate' => ['required_with:new_vehicle', 'string'],
            'new_vehicle.brand' => ['required_with:new_vehicle', 'string', 'max:255'],
            'new_vehicle.model' => ['required_with:new_vehicle', 'string', 'max:255'],
            'new_vehicle.vehicle_type' => ['required_with:new_vehicle', 'string', 'in:car,motorcycle,truck,van,bus'],
            'new_vehicle.year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'new_vehicle.color' => ['nullable', 'string', 'max:50'],
            'new_vehicle.vin' => ['nullable', 'string', 'max:17'],
            'new_vehicle.fuel' => ['nullable', 'string', 'in:gasoline,ethanol,diesel,flex,electric,hybrid'],
            'new_vehicle.transmission' => ['nullable', 'string', 'in:manual,automatic,semi-automatic'],
            'new_vehicle.mileage' => ['nullable', 'integer', 'min:0'],
            'new_vehicle.cilinder_capacity' => ['nullable', 'string', 'max:10'],
            'new_vehicle.observations' => ['nullable', 'string', 'max:5000'],

            // Flag de transferência
            'transfer_vehicle' => ['nullable', 'boolean'],

            // Dados da OS
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'observations' => ['nullable', 'string', 'max:5000'],
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            // Cliente
            'client_id.required_without' => 'O cliente é obrigatório quando não há dados de novo cliente.',
            'client_id.exists' => 'O cliente selecionado não existe.',
            'new_client.required_without' => 'Dados do novo cliente são obrigatórios quando não há client_id.',
            'new_client.name.required_with' => 'O nome do cliente é obrigatório.',
            'new_client.email.required_with' => 'O email do cliente é obrigatório.',
            'new_client.email.email' => 'O email do cliente deve ser válido.',
            'new_client.document_number.required_with' => 'O CPF/CNPJ é obrigatório.',
            'new_client.document_number.cpf_ou_cnpj' => 'O CPF/CNPJ informado é inválido.',
            'new_client.phone.required_with' => 'O telefone é obrigatório.',

            // Veículo
            'vehicle_id.required_without' => 'O veículo é obrigatório quando não há dados de novo veículo.',
            'vehicle_id.exists' => 'O veículo selecionado não existe.',
            'new_vehicle.required_without' => 'Dados do novo veículo são obrigatórios quando não há vehicle_id.',
            'new_vehicle.license_plate.required_with' => 'A placa do veículo é obrigatória.',
            'new_vehicle.brand.required_with' => 'A marca do veículo é obrigatória.',
            'new_vehicle.model.required_with' => 'O modelo do veículo é obrigatório.',
            'new_vehicle.vehicle_type.required_with' => 'O tipo do veículo é obrigatório.',
            'new_vehicle.vehicle_type.in' => 'O tipo do veículo deve ser: car, motorcycle, truck, van ou bus.',

            // OS
            'diagnosis.max' => 'O diagnóstico não pode ter mais de 5000 caracteres.',
            'observations.max' => 'As observações não podem ter mais de 5000 caracteres.',
            'technician_id.exists' => 'O técnico selecionado não existe.',
            'discount.numeric' => 'O desconto deve ser um valor numérico.',
            'discount.min' => 'O desconto não pode ser negativo.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Valida exclusividade: client_id XOR new_client
            if ($this->filled('client_id') && $this->filled('new_client')) {
                $validator->errors()->add('client_id',
                    'Não é possível enviar client_id e new_client simultaneamente.');
            }

            // Valida exclusividade: vehicle_id XOR new_vehicle
            if ($this->filled('vehicle_id') && $this->filled('new_vehicle')) {
                $validator->errors()->add('vehicle_id',
                    'Não é possível enviar vehicle_id e new_vehicle simultaneamente.');
            }
        });
    }
}
