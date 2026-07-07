<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole('Admin');

        $kasir = User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('password123'),
            ]
        );
        $kasir->assignRole('Kasir');
    }
}