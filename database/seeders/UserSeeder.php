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
                'password' => Hash::make('admin'),
            ]
        );
        $admin->assignRole('Admin');

        $kasir = User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('kasir'),
            ]
        );
        $kasir->assignRole('Kasir');

        $pegawai = User::firstOrCreate(
            ['username' => 'pegawai'],
            [
                'name' => 'Pegawai',
                'password' => Hash::make('pegawai'),
            ]
        );
        $pegawai->assignRole('Pegawai');
    }
}