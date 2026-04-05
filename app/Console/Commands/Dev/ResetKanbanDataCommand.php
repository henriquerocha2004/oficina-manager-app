<?php

namespace App\Console\Commands\Dev;

use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetKanbanDataCommand extends Command
{
    protected $signature = 'dev:kanban-reset {--count=15 : Number of draft orders to create}';
    protected $description = '[DEV] Deletes all service orders and creates draft-only orders for Kanban testing';

    public function handle(): void
    {
        $count = (int) $this->option('count');

        DB::setDefaultConnection('tenant');

        $this->info('Removing existing service orders...');
        DB::table('service_order_payments')->delete();
        DB::table('service_order_events')->delete();
        DB::table('service_order_items')->delete();
        DB::table('service_orders')->delete();

        $this->info("Creating {$count} DRAFT service orders...");
        $vehicles = Vehicle::all();

        foreach (range(1, $count) as $ignored) {
            $vehicle = $vehicles->random();
            $clientId = $vehicle->currentOwner?->client?->id ?? $vehicle->clients()->first()?->id;

            ServiceOrder::factory()->draft()->create([
                'vehicle_id' => $vehicle->id,
                'client_id' => $clientId,
                'total_parts' => 0,
                'total_services' => 0,
                'discount' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'outstanding_balance' => 0,
            ]);
        }

        $this->info("Done! {$count} DRAFT service orders created.");
    }
}
