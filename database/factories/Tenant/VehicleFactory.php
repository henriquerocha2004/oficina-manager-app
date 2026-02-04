<?php

namespace Database\Factories\Tenant;

use Illuminate\Support\Str;
use App\Models\Tenant\Vehicle;
use App\Models\Tenant\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $faker = $this->faker;
        $vehicleType = $faker->randomElement(['car', 'motorcycle']);
        $year = $faker->numberBetween(2000, date('Y'));

        // Generate Brazilian license plate (old or Mercosul format)
        $useMercosul = $faker->boolean(50);
        if ($useMercosul) {
            // Mercosul format: ABC1D23
            $licensePlate = strtoupper($faker->lexify('???')) . 
                           $faker->numberBetween(0, 9) . 
                           strtoupper($faker->randomLetter()) . 
                           $faker->numerify('##');
        } else {
            // Old format: ABC-1234
            $licensePlate = strtoupper($faker->lexify('???')) . '-' . 
                           $faker->numerify('####');
        }

        return [
            'id' => (string) Str::ulid(),
            'license_plate' => $licensePlate,
            'brand' => $faker->randomElement(['Toyota', 'Honda', 'Ford', 'Chevrolet', 'Volkswagen', 'Fiat', 'Hyundai', 'Nissan', 'Renault', 'Jeep']),
            'model' => $faker->randomElement(['Corolla', 'Civic', 'Fusion', 'Onix', 'Gol', 'Uno', 'HB20', 'Kicks', 'Sandero', 'Compass']),
            'year' => $year,
            'color' => $faker->optional()->randomElement(['Branco', 'Preto', 'Prata', 'Vermelho', 'Azul', 'Cinza']),
            'vin' => $faker->optional()->regexify('[A-HJ-NPR-Z0-9]{17}'),
            'fuel' => $faker->optional()->randomElement(['gasoline', 'alcohol', 'diesel']),
            'transmission' => $faker->optional()->randomElement(['manual', 'automatic']),
            'mileage' => $faker->optional()->numberBetween(0, 200000),
            'cilinder_capacity' => $vehicleType === 'motorcycle' ? $faker->optional()->randomElement(['125cc', '150cc', '160cc', '250cc', '300cc']) : null,
            'client_id' => Client::factory(),
            'vehicle_type' => $vehicleType,
            'observations' => $faker->optional()->sentence(),
        ];
    }
}
