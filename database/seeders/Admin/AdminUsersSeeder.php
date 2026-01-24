<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\AdminUsers;
use Illuminate\Database\Seeder;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (AdminUsers::count() > 0) {
            return;
        }

        $password = 'password';

        AdminUsers::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt($password),
        ]);
    }
}
