<?php

namespace Database\Seeders;

use App\Models\RewardKatalog;
use Illuminate\Database\Seeder;

class RewardKatalogSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            [
                'nama' => 'Voucher Diskon Rp10.000',
                'jenis' => 'voucher',
                'jenis_potongan' => 'nominal',
                'nominal_potongan' => 10000,
                'min_pembelian' => 30000,
                'deskripsi' => 'Potongan langsung Rp10.000 untuk transaksi berikutnya, minimal belanja Rp30.000.',
                'poin_dibutuhkan' => 50,
                'stok' => null,
            ],
            [
                'nama' => 'Voucher Diskon Rp25.000',
                'jenis' => 'voucher',
                'jenis_potongan' => 'nominal',
                'nominal_potongan' => 25000,
                'min_pembelian' => 75000,
                'deskripsi' => 'Potongan langsung Rp25.000, minimal belanja Rp75.000.',
                'poin_dibutuhkan' => 120,
                'stok' => null,
            ],
            [
                'nama' => 'Voucher Diskon 20%',
                'jenis' => 'voucher',
                'jenis_potongan' => 'persentase',
                'persentase' => 20,
                'maksimal_potongan' => 40000,
                'min_pembelian' => 0,
                'deskripsi' => 'Diskon 20% untuk seluruh menu, maksimal potongan Rp40.000.',
                'poin_dibutuhkan' => 250,
                'stok' => null,
            ],
        ];

        foreach ($rewards as $reward) {
            RewardKatalog::firstOrCreate(
                ['nama' => $reward['nama']],
                $reward + ['status_aktif' => true]
            );
        }
    }
}
