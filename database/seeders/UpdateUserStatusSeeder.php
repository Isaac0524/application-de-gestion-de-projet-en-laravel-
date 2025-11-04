<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateUserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::whereNull('status')->update(['status' => 'active']);
    }
}
