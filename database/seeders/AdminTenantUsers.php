<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminTenantUsers extends Seeder
{
    public function run(string $name, string $email): void
    {
       $password = 'password';

       User::query()->create([
          'name' => $name,
          'email' => $email,
          'password' => Hash::make($password),
       ]);
    }
}
