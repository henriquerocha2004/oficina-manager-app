<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::factory()->count(20)->create();
    }
}
