<?php

namespace Database\Factories\Tenant;

use Illuminate\Support\Str;
use App\Models\Tenant\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $faker = $this->faker;
        $categories = Service::getCategories();

        return [
            'id' => strtolower((string) Str::ulid()),
            'name' => $faker->unique()->words(3, true) . ' - ' . $faker->numberBetween(1, 10000),
            'description' => $faker->optional(0.7)->sentence(10),
            'base_price' => $faker->randomFloat(2, 50, 5000),
            'category' => $faker->randomElement($categories),
            'estimated_time' => $faker->optional(0.8)->numberBetween(30, 480),
            'is_active' => $faker->boolean(90),
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Service::CATEGORY_MAINTENANCE,
        ]);
    }

    public function repair(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Service::CATEGORY_REPAIR,
        ]);
    }

    public function diagnostic(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Service::CATEGORY_DIAGNOSTIC,
        ]);
    }

    public function painting(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Service::CATEGORY_PAINTING,
        ]);
    }

    public function alignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Service::CATEGORY_ALIGNMENT,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
