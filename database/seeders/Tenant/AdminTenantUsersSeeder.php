<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminTenantUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->count() > 0) {
            return;
        }

         $password = 'password';

         User::factory()->create([
            'name' => 'Admin Tenant User',
            'email' => 'admin@tenant.com',
            'password' => Hash::make($password),
         ]);
    }
}
