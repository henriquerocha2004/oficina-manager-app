<?php

namespace Database\Factories\Admin;

use App\Models\Admin\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $uid = uniqid();

        return [
            'id' => (string) Str::ulid(),
            'name' => fake()->company(),
            'email' => "client-{$uid}@test.com",
            'document' => $uid . fake()->numerify('####'),
            'phone' => fake()->phoneNumber(),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->numerify('########'),
        ];
    }
}
