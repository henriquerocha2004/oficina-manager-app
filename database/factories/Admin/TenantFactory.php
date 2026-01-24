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
        return [
            'name' => fake()->company(),
            'domain' => fake()->unique()->domainName(),
            'database_name' => 'tenant_' . fake()->unique()->numerify('####'),
            'is_active' => true,
            'email' => fake()->unique()->safeEmail(),
            'document' => fake()->numerify('##########'),
        ];
    }
}
