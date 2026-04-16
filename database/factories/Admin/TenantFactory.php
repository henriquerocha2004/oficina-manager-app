<?php

namespace Database\Factories\Admin;

use App\Models\Admin\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uid = uniqid();

        return [
            'name' => fake()->company(),
            'domain' => 'tst' . $uid,
            'database_name' => 'tnt_' . $uid,
            'is_active' => true,
            'email' => "{$uid}@test.com",
            'document' => fake()->numerify('##########'),
            'status' => 'active',
            'trial_until' => null,
            'client_id' => null,
        ];
    }

    public function trial(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'trial',
            'trial_until' => now()->addDays(30),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function expiredTrial(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'trial',
            'trial_until' => now()->subDay(),
        ]);
    }
}
