<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

class BahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua satuan sekali di awal, index berdasarkan kolom `nama` (huruf kecil semua)
        // -- sesuai kolom yang dipakai SatuanSeeder -- supaya tidak sensitif huruf besar/kecil
        // dan tidak bergantung pada urutan ID.
        $satuans = Satuan::all()->keyBy(function ($satuan) {
            return strtolower($satuan->nama);
        });

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
                'satuan' => 'gram',
                'stok' => 5000.00,
                'stok_minimum' => 1000.00,
            ],
            [
                'nama_bahan' => 'Garam',
                'satuan' => 'gram',
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
                'satuan' => 'pcs',
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
                'satuan' => 'gram',
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
            $namaSatuan = strtolower($bahan['satuan']);
            unset($bahan['satuan']);

            $satuan = $satuans->get($namaSatuan);

            if (! $satuan) {
                $this->command?->warn(
                    "Satuan '{$namaSatuan}' tidak ditemukan di tabel satuans, '{$bahan['nama_bahan']}' dilewati."
                );

                continue;
            }

            $bahan['satuan_id'] = $satuan->id;

            BahanBaku::updateOrCreate(
                ['nama_bahan' => $bahan['nama_bahan']],
                $bahan
            );
        }
    }
}