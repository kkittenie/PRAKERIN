<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use Illuminate\Database\Seeder;

class BahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bahanBakus = [
            [
                'nama_bahan' => 'Tepung Terigu Protein Tinggi',
                'satuan' => 'kg',
                'stok' => 50.00,
                'stok_minimum' => 10.00,
            ],
            [
                'nama_bahan' => 'Tepung Terigu Protein Sedang',
                'satuan' => 'kg',
                'stok' => 30.00,
                'stok_minimum' => 5.00,
            ],
            [
                'nama_bahan' => 'Mentega / Butter',
                'satuan' => 'kg',
                'stok' => 25.00,
                'stok_minimum' => 5.00,
            ],
            [
                'nama_bahan' => 'Gula Pasir',
                'satuan' => 'kg',
                'stok' => 40.00,
                'stok_minimum' => 8.00,
            ],
            [
                'nama_bahan' => 'Ragi Instan',
                'satuan' => 'gr',
                'stok' => 5000.00,
                'stok_minimum' => 1000.00,
            ],
            [
                'nama_bahan' => 'Garam',
                'satuan' => 'gr',
                'stok' => 2000.00,
                'stok_minimum' => 500.00,
            ],
            [
                'nama_bahan' => 'Susu Cair UHT',
                'satuan' => 'liter',
                'stok' => 24.00,
                'stok_minimum' => 6.00,
            ],
            [
                'nama_bahan' => 'Telur Ayam',
                'satuan' => 'butir',
                'stok' => 150.00,
                'stok_minimum' => 30.00,
            ],
            [
                'nama_bahan' => 'Cokelat Batang (Dark Chocolate)',
                'satuan' => 'kg',
                'stok' => 15.00,
                'stok_minimum' => 3.00,
            ],
            [
                'nama_bahan' => 'Kacang Almond Iris',
                'satuan' => 'kg',
                'stok' => 5.00,
                'stok_minimum' => 1.00,
            ],
            [
                'nama_bahan' => 'Selai Blueberry',
                'satuan' => 'gr',
                'stok' => 3000.00,
                'stok_minimum' => 500.00,
            ],
            [
                'nama_bahan' => 'Cream Cheese',
                'satuan' => 'kg',
                'stok' => 10.00,
                'stok_minimum' => 2.00,
            ],
        ];

        foreach ($bahanBakus as $bahan) {
            BahanBaku::updateOrCreate(
                ['nama_bahan' => $bahan['nama_bahan']],
                $bahan
            );
        }
    }
}
