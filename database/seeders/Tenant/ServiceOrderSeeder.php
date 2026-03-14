<?php

namespace Database\Seeders\Tenant;

use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderItem;
use App\Models\Tenant\Vehicle;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::all();

        if ($vehicles->isEmpty()) {
            $vehicles = Vehicle::factory()->count(20)->create();
        }

        $services = Service::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        $statuses = [
            ServiceOrderStatusEnum::DRAFT,
            ServiceOrderStatusEnum::WAITING_APPROVAL,
            ServiceOrderStatusEnum::APPROVED,
            ServiceOrderStatusEnum::IN_PROGRESS,
            ServiceOrderStatusEnum::COMPLETED,
        ];

        foreach (range(1, 30) as $index) {
            $vehicle = $vehicles->random();
            $status = $statuses[array_rand($statuses)];
            $clientId = $vehicle->currentOwner?->client?->id ?? $vehicle->clients()->first()?->id;

            $serviceOrder = ServiceOrder::factory()->create([
                'vehicle_id' => $vehicle->id,
                'client_id' => $clientId,
                'status' => $status,
            ]);

            if ($services->isNotEmpty()) {
                $numServices = rand(1, 4);
                $selectedServices = $services->random(min($numServices, $services->count()));

                if (! $selectedServices instanceof \Illuminate\Database\Eloquent\Collection) {
                    $selectedServices = collect([$selectedServices]);
                }

                foreach ($selectedServices as $service) {
                    $quantity = rand(1, 2);
                    $unitPrice = $service->base_price;

                    ServiceOrderItem::create([
                        'service_order_id' => $serviceOrder->id,
                        'type' => ServiceOrderItemTypeEnum::SERVICE,
                        'service_id' => $service->id,
                        'description' => $service->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $quantity * $unitPrice,
                    ]);
                }
            }

            if ($products->isNotEmpty() && rand(0, 1)) {
                $numProducts = rand(1, 5);
                $selectedProducts = $products->random(min($numProducts, $products->count()));

                if (! $selectedProducts instanceof \Illuminate\Database\Eloquent\Collection) {
                    $selectedProducts = collect([$selectedProducts]);
                }

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $unitPrice = $product->sell_price ?: $product->cost_price ?: 100;

                    ServiceOrderItem::create([
                        'service_order_id' => $serviceOrder->id,
                        'type' => ServiceOrderItemTypeEnum::PART,
                        'product_id' => $product->id,
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $quantity * $unitPrice,
                    ]);
                }
            }
        }
    }
}
