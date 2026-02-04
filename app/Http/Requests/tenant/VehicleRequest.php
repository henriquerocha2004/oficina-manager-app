<?php

namespace App\Http\Requests\tenant;

use App\Dto\VehicleDto;
use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand' => ['required', 'string', 'min:3', 'max:255'],
            'model' => ['required', 'string', 'min:1', 'max:255'],
            'year' => ['required', 'integer', 'min:1886', 'max:' . (date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'client_id' => ['required', 'string', 'max:26'],
            'license_plate' => ['required', 'string', 'regex:/^[A-Z]{3}-?[0-9]{1}[A-Z0-9][0-9]{2}$/'],
            'vehicle_type' => ['nullable', 'string', 'in:car,motorcycle'],
            'cilinder_capacity' => ['nullable', 'string', 'max:50'],
            'fuel' => ['nullable', 'string', 'in:alcohol,gasoline,diesel'],
            'transmission' => ['nullable', 'string', 'in:manual,automatic'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'vin' => ['nullable', 'string', 'max:17'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand.required' => 'A marca é obrigatória.',
            'brand.min' => 'A marca deve ter no mínimo 3 caracteres.',
            'brand.max' => 'A marca não pode ter mais de 255 caracteres.',
            'model.required' => 'O modelo é obrigatório.',
            'model.min' => 'O modelo deve ter no mínimo 1 caractere.',
            'model.max' => 'O modelo não pode ter mais de 255 caracteres.',
            'year.required' => 'O ano é obrigatório.',
            'year.integer' => 'O ano deve ser um número inteiro.',
            'year.min' => 'O ano deve ser maior ou igual a 1886.',
            'year.max' => 'O ano não pode ser maior que ' . (date('Y') + 1) . '.',
            'color.max' => 'A cor não pode ter mais de 50 caracteres.',
            'client_id.required' => 'O cliente é obrigatório.',
            'client_id.max' => 'O ID do cliente não pode ter mais de 26 caracteres.',
            'license_plate.required' => 'A placa é obrigatória.',
            'license_plate.regex' => 'A placa deve estar no formato ABC-1234 ou ABC1D23 (Mercosul).',
            'vehicle_type.in' => 'O tipo de veículo deve ser carro ou motocicleta.',
            'cilinder_capacity.max' => 'A capacidade do cilindro não pode ter mais de 50 caracteres.',
            'fuel.in' => 'O combustível deve ser álcool, gasolina ou diesel.',
            'transmission.in' => 'A transmissão deve ser manual ou automática.',
            'mileage.integer' => 'A quilometragem deve ser um número inteiro.',
            'mileage.min' => 'A quilometragem não pode ser negativa.',
            'vin.max' => 'O VIN não pode ter mais de 17 caracteres.',
            'observations.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }

    public function toDto(): VehicleDto
    {
        return VehicleDto::fromArray($this->validated());
    }
}
