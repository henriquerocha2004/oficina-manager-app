<?php

namespace Database\Factories\Tenant;

use Illuminate\Support\Str;
use App\Models\Tenant\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $faker = $this->faker;
        return [
            'id' => (string) Str::ulid(),
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'document_number' => $faker->unique()->numerify('###########'),
            'street' => $faker->streetName(),
            'city' => $faker->city(),
            'state' => $faker->stateAbbr(),
            'zip_code' => $faker->postcode(),
            'phone' => $faker->phoneNumber(),
            'observations' => $faker->optional()->sentence(),
        ];
    }
}
