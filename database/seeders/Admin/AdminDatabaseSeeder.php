<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class AdminDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUsersSeeder::class,
            TenantSeeder::class,
        ]);
    }
}
