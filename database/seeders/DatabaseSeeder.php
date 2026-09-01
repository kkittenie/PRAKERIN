<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            UserSeeder::class,
            SatuanSeeder::class,
            BahanBakuSeeder::class,
            ProdukSeeder::class,
            ProdukKhususBundlingSeeder::class,
            RewardKatalogSeeder::class,
            StampProgramSeeder::class,
            KontenStatisSeeder::class,
            MemberLevelSeeder::class,
        ]);
    }
}