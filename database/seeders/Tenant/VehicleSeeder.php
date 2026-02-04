<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\Vehicle;
use App\Models\Tenant\Client;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();

        if ($clients->isEmpty()) {
            $clients = Client::factory()->count(10)->create();
        }

        foreach (range(1, 50) as $index) {
            Vehicle::factory()->create([
                'client_id' => $clients->random()->id,
            ]);
        }
    }
}
