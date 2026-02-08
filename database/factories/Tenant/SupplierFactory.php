<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        $faker = $this->faker;

        // Lista de nomes de empresas brasileiras fictícias
        $companyNames = [
            'Auto Peças Brasil',
            'Mecânica Central',
            'Distribuidora Nacional',
            'Importadora Premium',
            'Comercial Automotiva',
            'Peças e Acessórios Ltda',
            'Distribuidora Master',
            'Automotiva Express',
            'Peças Rápidas',
            'Fornecedor Premium',
        ];

        $companyName = $faker->randomElement($companyNames).' '.$faker->company();
        $tradeName = $faker->boolean(70) ? $faker->companySuffix().' '.$faker->words(2, true) : null;

        return [
            'id' => (string) Str::ulid(),
            'name' => $companyName,
            'trade_name' => $tradeName,
            'document_number' => $this->generateCnpj(),
            'email' => $faker->unique()->companyEmail(),
            'phone' => $faker->boolean(80) ? $this->generateBrazilianPhone() : null,
            'mobile' => $faker->boolean(60) ? $this->generateBrazilianMobile() : null,
            'website' => $faker->boolean(40) ? $faker->url() : null,
            'street' => $faker->streetName(),
            'number' => $faker->buildingNumber(),
            'complement' => $faker->boolean(30) ? $faker->secondaryAddress() : null,
            'neighborhood' => $faker->citySuffix(),
            'city' => $faker->city(),
            'state' => $faker->stateAbbr(),
            'zip_code' => $this->generateBrazilianZipCode(),
            'contact_person' => $faker->boolean(70) ? $faker->name() : null,
            'payment_term_days' => $faker->randomElement([0, 7, 15, 30, 45, 60, 90]),
            'notes' => $faker->boolean(30) ? $faker->sentence() : null,
            'is_active' => $faker->boolean(90),
        ];
    }

    /**
     * Generate a valid Brazilian CNPJ
     */
    private function generateCnpj(): string
    {
        $n1 = rand(0, 9);
        $n2 = rand(0, 9);
        $n3 = rand(0, 9);
        $n4 = rand(0, 9);
        $n5 = rand(0, 9);
        $n6 = rand(0, 9);
        $n7 = rand(0, 9);
        $n8 = rand(0, 9);
        $n9 = 0;
        $n10 = 0;
        $n11 = 0;
        $n12 = 1;

        $d1 = $n12 * 2 + $n11 * 3 + $n10 * 4 + $n9 * 5 + $n8 * 6 + $n7 * 7 + $n6 * 8 + $n5 * 9 + $n4 * 2 + $n3 * 3 + $n2 * 4 + $n1 * 5;
        $d1 = 11 - ($d1 % 11);
        $d1 = ($d1 >= 10) ? 0 : $d1;

        $d2 = $d1 * 2 + $n12 * 3 + $n11 * 4 + $n10 * 5 + $n9 * 6 + $n8 * 7 + $n7 * 8 + $n6 * 9 + $n5 * 2 + $n4 * 3 + $n3 * 4 + $n2 * 5 + $n1 * 6;
        $d2 = 11 - ($d2 % 11);
        $d2 = ($d2 >= 10) ? 0 : $d2;

        return sprintf('%d%d%d%d%d%d%d%d%d%d%d%d%d%d', $n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9, $n10, $n11, $n12, $d1, $d2);
    }

    /**
     * Generate a Brazilian phone number
     */
    private function generateBrazilianPhone(): string
    {
        return sprintf('(%d%d) %d%d%d%d-%d%d%d%d',
            rand(1, 9), rand(1, 9),
            rand(2, 5), rand(0, 9), rand(0, 9), rand(0, 9),
            rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9)
        );
    }

    /**
     * Generate a Brazilian mobile number
     */
    private function generateBrazilianMobile(): string
    {
        return sprintf('(%d%d) 9%d%d%d%d-%d%d%d%d',
            rand(1, 9), rand(1, 9),
            rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9),
            rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9)
        );
    }

    /**
     * Generate a Brazilian ZIP code
     */
    private function generateBrazilianZipCode(): string
    {
        return sprintf('%d%d%d%d%d-%d%d%d',
            rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9),
            rand(0, 9), rand(0, 9), rand(0, 9)
        );
    }
}
