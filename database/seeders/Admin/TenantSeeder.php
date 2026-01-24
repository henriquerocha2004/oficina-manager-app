<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Tenant::count() > 0) {
            return;
        }
        Tenant::factory()->create([
            "database_name" => "dev_tentant",
        ]);
    }
}
