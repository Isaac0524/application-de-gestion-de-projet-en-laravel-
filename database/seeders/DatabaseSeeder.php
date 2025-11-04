<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::create([
            'name' => 'MANAGER PRINCIPAL',
            'email' => 'manager01@pm-app.com',
            'password' => Hash::make('@manager01'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);
        User::create([
            'name' => 'MANAGER Second',
            'email' => 'manager02@pm-app.com',
            'password' => Hash::make('@manager02'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);
    }
}
