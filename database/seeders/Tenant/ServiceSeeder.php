<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $commonServices = [
            [
                'name' => 'Troca de óleo',
                'description' => 'Troca completa de óleo do motor com filtro',
                'base_price' => 150.00,
                'category' => Service::CATEGORY_MAINTENANCE,
                'estimated_time' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Alinhamento e balanceamento',
                'description' => 'Alinhamento completo das rodas com balanceamento',
                'base_price' => 120.00,
                'category' => Service::CATEGORY_ALIGNMENT,
                'estimated_time' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Revisão completa',
                'description' => 'Revisão geral do veículo conforme manual do fabricante',
                'base_price' => 450.00,
                'category' => Service::CATEGORY_MAINTENANCE,
                'estimated_time' => 240,
                'is_active' => true,
            ],
            [
                'name' => 'Troca de pastilhas de freio',
                'description' => 'Substituição das pastilhas de freio dianteiras e traseiras',
                'base_price' => 280.00,
                'category' => Service::CATEGORY_REPAIR,
                'estimated_time' => 120,
                'is_active' => true,
            ],
            [
                'name' => 'Diagnóstico eletrônico',
                'description' => 'Diagnóstico completo do sistema eletrônico do veículo',
                'base_price' => 100.00,
                'category' => Service::CATEGORY_DIAGNOSTIC,
                'estimated_time' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'Geometria',
                'description' => 'Ajuste de geometria das rodas',
                'base_price' => 90.00,
                'category' => Service::CATEGORY_ALIGNMENT,
                'estimated_time' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Troca de filtros',
                'description' => 'Substituição de filtros de ar, combustível e cabine',
                'base_price' => 80.00,
                'category' => Service::CATEGORY_MAINTENANCE,
                'estimated_time' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Pintura completa',
                'description' => 'Pintura completa da lataria do veículo',
                'base_price' => 3500.00,
                'category' => Service::CATEGORY_PAINTING,
                'estimated_time' => 2880,
                'is_active' => true,
            ],
            [
                'name' => 'Funilaria',
                'description' => 'Reparo de amassados e danos na lataria',
                'base_price' => 500.00,
                'category' => Service::CATEGORY_REPAIR,
                'estimated_time' => 360,
                'is_active' => true,
            ],
            [
                'name' => 'Polimento',
                'description' => 'Polimento e cristalização da pintura',
                'base_price' => 250.00,
                'category' => Service::CATEGORY_PAINTING,
                'estimated_time' => 180,
                'is_active' => true,
            ],
            [
                'name' => 'Troca de correia dentada',
                'description' => 'Substituição da correia dentada do motor',
                'base_price' => 350.00,
                'category' => Service::CATEGORY_MAINTENANCE,
                'estimated_time' => 180,
                'is_active' => true,
            ],
            [
                'name' => 'Limpeza de bicos injetores',
                'description' => 'Limpeza e teste dos bicos injetores',
                'base_price' => 200.00,
                'category' => Service::CATEGORY_MAINTENANCE,
                'estimated_time' => 120,
                'is_active' => true,
            ],
            [
                'name' => 'Carga de ar-condicionado',
                'description' => 'Recarga completa do sistema de ar-condicionado',
                'base_price' => 180.00,
                'category' => Service::CATEGORY_MAINTENANCE,
                'estimated_time' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Troca de bateria',
                'description' => 'Substituição da bateria do veículo',
                'base_price' => 350.00,
                'category' => Service::CATEGORY_REPAIR,
                'estimated_time' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Alinhamento a laser',
                'description' => 'Alinhamento de precisão utilizando tecnologia a laser',
                'base_price' => 150.00,
                'category' => Service::CATEGORY_ALIGNMENT,
                'estimated_time' => 75,
                'is_active' => true,
            ],
        ];

        foreach ($commonServices as $serviceData) {
            Service::create($serviceData);
        }

        Service::factory()->count(10)->create();
    }
}
