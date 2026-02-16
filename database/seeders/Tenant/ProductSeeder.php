<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Product;
use App\Models\Tenant\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $commonProducts = [
            [
                'name' => 'Óleo de Motor 5W30 Sintético',
                'description' => 'Óleo de motor totalmente sintético 5W30 para motores modernos',
                'sku' => 'OIL-5W30-1L',
                'barcode' => '7891234567890',
                'manufacturer' => 'Mobil',
                'category' => Product::CATEGORY_FLUIDS,
                'unit' => Product::UNIT_LITER,
                'unit_price' => 45.00,
                'suggested_price' => 89.90,
                'min_stock_level' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Filtro de Ar Mann C24005',
                'description' => 'Filtro de ar de alta eficiência para diversos modelos',
                'sku' => 'FIL-AR-C24005',
                'barcode' => '7891234567891',
                'manufacturer' => 'Mann Filter',
                'category' => Product::CATEGORY_FILTERS,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 35.00,
                'suggested_price' => 69.90,
                'min_stock_level' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Pastilha de Freio Dianteira TRW',
                'description' => 'Pastilha de freio dianteira cerâmica premium',
                'sku' => 'BRK-PAD-TRW01',
                'barcode' => '7891234567892',
                'manufacturer' => 'TRW',
                'category' => Product::CATEGORY_BRAKES,
                'unit' => Product::UNIT_BOX,
                'unit_price' => 120.00,
                'suggested_price' => 249.90,
                'min_stock_level' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Disco de Freio Ventilado',
                'description' => 'Disco de freio ventilado para carros médios e grandes',
                'sku' => 'BRK-DSC-VEN280',
                'barcode' => '7891234567893',
                'manufacturer' => 'Fremax',
                'category' => Product::CATEGORY_BRAKES,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 95.00,
                'suggested_price' => 189.90,
                'min_stock_level' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Vela de Ignição NGK Iridium',
                'description' => 'Vela de ignição com ponta de irídio para melhor desempenho',
                'sku' => 'IGN-VEL-NGK-IR',
                'barcode' => '7891234567894',
                'manufacturer' => 'NGK',
                'category' => Product::CATEGORY_ELECTRICAL,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 42.00,
                'suggested_price' => 79.90,
                'min_stock_level' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Bateria Automotiva 60Ah Moura',
                'description' => 'Bateria automotiva 60 amperes com 18 meses de garantia',
                'sku' => 'BAT-60AH-MR',
                'barcode' => '7891234567895',
                'manufacturer' => 'Moura',
                'category' => Product::CATEGORY_ELECTRICAL,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 320.00,
                'suggested_price' => 589.90,
                'min_stock_level' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Correia Dentada Gates PowerGrip',
                'description' => 'Correia dentada de alta resistência',
                'sku' => 'ENG-COR-GATES',
                'barcode' => '7891234567896',
                'manufacturer' => 'Gates',
                'category' => Product::CATEGORY_ENGINE,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 85.00,
                'suggested_price' => 169.90,
                'min_stock_level' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Amortecedor Monroe Dianteiro',
                'description' => 'Amortecedor a gás dianteiro para carros de passeio',
                'sku' => 'SUS-AMO-MON-D',
                'barcode' => '7891234567897',
                'manufacturer' => 'Monroe',
                'category' => Product::CATEGORY_SUSPENSION,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 180.00,
                'suggested_price' => 349.90,
                'min_stock_level' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Pneu Goodyear 185/65R15',
                'description' => 'Pneu radial para carros de passeio',
                'sku' => 'TIR-GOO-185-65-15',
                'barcode' => '7891234567898',
                'manufacturer' => 'Goodyear',
                'category' => Product::CATEGORY_TIRES,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 280.00,
                'suggested_price' => 499.90,
                'min_stock_level' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Lâmpada H4 Philips Vision',
                'description' => 'Lâmpada halógena H4 com 30% mais iluminação',
                'sku' => 'ELE-LAM-H4-PHI',
                'barcode' => '7891234567899',
                'manufacturer' => 'Philips',
                'category' => Product::CATEGORY_ELECTRICAL,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 28.00,
                'suggested_price' => 54.90,
                'min_stock_level' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Fluido de Freio DOT 4',
                'description' => 'Fluido de freio sintético DOT 4 de alta performance',
                'sku' => 'FLU-FRE-DOT4',
                'barcode' => '7891234567800',
                'manufacturer' => 'Bosch',
                'category' => Product::CATEGORY_FLUIDS,
                'unit' => Product::UNIT_LITER,
                'unit_price' => 22.00,
                'suggested_price' => 39.90,
                'min_stock_level' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Aditivo de Radiador Concentrado',
                'description' => 'Aditivo de radiador orgânico concentrado 1L',
                'sku' => 'FLU-RAD-ADI',
                'barcode' => '7891234567801',
                'manufacturer' => 'Wurth',
                'category' => Product::CATEGORY_FLUIDS,
                'unit' => Product::UNIT_LITER,
                'unit_price' => 18.00,
                'suggested_price' => 34.90,
                'min_stock_level' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Limpador de Para-brisa Bosch Aerotwin',
                'description' => 'Limpador de para-brisa sem armação 24 polegadas',
                'sku' => 'ACC-LIM-BOH-24',
                'barcode' => '7891234567802',
                'manufacturer' => 'Bosch',
                'category' => Product::CATEGORY_OTHER,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 55.00,
                'suggested_price' => 99.90,
                'min_stock_level' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Filtro de Combustível Mahle',
                'description' => 'Filtro de combustível para motores flex',
                'sku' => 'FIL-COM-MAH',
                'barcode' => '7891234567803',
                'manufacturer' => 'Mahle',
                'category' => Product::CATEGORY_FILTERS,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 32.00,
                'suggested_price' => 59.90,
                'min_stock_level' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Filtro de Óleo Tecfil PSL140',
                'description' => 'Filtro de óleo de alta eficiência',
                'sku' => 'FIL-OLE-TEC140',
                'barcode' => '7891234567804',
                'manufacturer' => 'Tecfil',
                'category' => Product::CATEGORY_FILTERS,
                'unit' => Product::UNIT_UNIT,
                'unit_price' => 25.00,
                'suggested_price' => 49.90,
                'min_stock_level' => 15,
                'is_active' => true,
            ],
        ];

        // Criar produtos comuns
        $createdProducts = [];
        foreach ($commonProducts as $productData) {
            $createdProducts[] = Product::create($productData);
        }

        // Criar produtos adicionais com factory
        $factoryProducts = Product::factory()->count(20)->create();
        $allProducts = array_merge($createdProducts, $factoryProducts->toArray());

        // Buscar fornecedores existentes
        $suppliers = Supplier::query()->where('is_active', true)->get();

        if ($suppliers->isEmpty()) {
            $this->command->warn('Nenhum fornecedor ativo encontrado. Execute SupplierSeeder primeiro.');

            return;
        }

        // Vincular fornecedores aos produtos
        foreach ($allProducts as $product) {
            // Garantir que temos um objeto Product
            if (is_array($product)) {
                $product = Product::find($product['id']);
            }

            // Cada produto terá de 1 a 3 fornecedores
            $numberOfSuppliers = rand(1, min(3, $suppliers->count()));
            $selectedSuppliers = $suppliers->random($numberOfSuppliers);

            foreach ($selectedSuppliers as $index => $supplier) {
                // Primeiro fornecedor tem 70% de chance de ser preferencial
                $isPreferred = ($index === 0 && rand(1, 100) <= 70);

                // Preço de custo varia entre 60% e 80% do preço unitário
                $costPrice = $product->unit_price * (rand(60, 80) / 100);

                // Gerar SKU seguro (apenas letras ASCII)
                $supplierPrefix = preg_replace('/[^A-Za-z]/', '', $supplier->name);
                $supplierPrefix = mb_strtoupper(mb_substr($supplierPrefix, 0, 3));
                if (strlen($supplierPrefix) < 3) {
                    $supplierPrefix = str_pad($supplierPrefix, 3, 'X');
                }

                $product->suppliers()->attach($supplier->id, [
                    'id' => strtolower((string) \Illuminate\Support\Str::ulid()),
                    'supplier_sku' => 'SUP-'.$supplierPrefix.'-'.rand(1000, 9999),
                    'cost_price' => round($costPrice, 2),
                    'lead_time_days' => rand(1, 30),
                    'min_order_quantity' => rand(1, 10),
                    'is_preferred' => $isPreferred,
                    'notes' => rand(1, 100) <= 30 ? 'Fornecedor com bom histórico de entregas' : null,
                ]);
            }
        }

        $this->command->info('Products seeded successfully with supplier relationships!');
        $this->command->info('Total products created: '.count($allProducts));
        $this->command->info('Suppliers linked to products: '.$suppliers->count());
    }
}
