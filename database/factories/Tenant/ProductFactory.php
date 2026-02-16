<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $faker = $this->faker;
        $categories = Product::getCategories();
        $units = Product::getUnits();

        $productNames = [
            'Óleo de Motor',
            'Filtro de Ar',
            'Pastilha de Freio',
            'Disco de Freio',
            'Vela de Ignição',
            'Bateria Automotiva',
            'Correia Dentada',
            'Amortecedor',
            'Pneu',
            'Lâmpada',
            'Fluido de Freio',
            'Aditivo de Radiador',
            'Limpador de Para-brisa',
            'Filtro de Combustível',
            'Filtro de Óleo',
            'Jogo de Cabos de Vela',
            'Bobina de Ignição',
            'Sensor de Oxigênio',
            'Rolamento',
            'Junta do Cabeçote',
        ];

        $manufacturers = [
            'Bosch',
            'NGK',
            'Continental',
            'Cofap',
            'TRW',
            'Monroe',
            'Mobil',
            'Shell',
            'Castrol',
            'Mann Filter',
            'Tecfil',
            'Mahle',
            'Petronas',
            'Valeo',
            'Delphi',
        ];

        $unitPrice = $faker->randomFloat(2, 10, 1500);
        $suggestedPrice = $faker->optional(0.8)->randomFloat(2, $unitPrice * 1.2, $unitPrice * 2.5);

        return [
            'id' => strtolower((string) Str::ulid()),
            'name' => $faker->randomElement($productNames).' '.$faker->bothify('##??'),
            'description' => $faker->optional(0.6)->sentence(8),
            'sku' => $faker->optional(0.7)->bothify('SKU-####-????'),
            'barcode' => $faker->optional(0.5)->ean13(),
            'manufacturer' => $faker->optional(0.8)->randomElement($manufacturers),
            'category' => $faker->randomElement($categories),
            'min_stock_level' => $faker->optional(0.7)->numberBetween(5, 50),
            'unit' => $faker->randomElement($units),
            'unit_price' => $unitPrice,
            'suggested_price' => $suggestedPrice,
            'is_active' => $faker->boolean(85),
        ];
    }

    public function engine(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Product::CATEGORY_ENGINE,
        ]);
    }

    public function suspension(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Product::CATEGORY_SUSPENSION,
        ]);
    }

    public function brakes(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Product::CATEGORY_BRAKES,
        ]);
    }

    public function electrical(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Product::CATEGORY_ELECTRICAL,
        ]);
    }

    public function filters(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Product::CATEGORY_FILTERS,
        ]);
    }

    public function fluids(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Product::CATEGORY_FLUIDS,
        ]);
    }

    public function tires(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => Product::CATEGORY_TIRES,
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
