<?php

namespace Database\Seeders;

use App\Models\RewardKatalog;
use Illuminate\Database\Seeder;

class RewardKatalogSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            ['nama' => 'Voucher Diskon Rp10.000', 'jenis' => 'voucher', 'deskripsi' => 'Potongan langsung Rp10.000 untuk transaksi berikutnya, minimal belanja Rp30.000.', 'poin_dibutuhkan' => 50, 'stok' => null],
            ['nama' => 'Es Kopi Susu Gratis', 'jenis' => 'produk', 'deskripsi' => 'Tukar poin kamu dengan satu gelas Es Kopi Susu ukuran reguler.', 'poin_dibutuhkan' => 80, 'stok' => 40],
            ['nama' => 'Voucher Diskon Rp25.000', 'jenis' => 'voucher', 'deskripsi' => 'Potongan langsung Rp25.000, minimal belanja Rp75.000.', 'poin_dibutuhkan' => 120, 'stok' => null],
            ['nama' => 'Cemilan Pendamping Gratis', 'jenis' => 'produk', 'deskripsi' => 'Satu porsi cemilan pilihan, gratis tanpa syarat minimal belanja.', 'poin_dibutuhkan' => 150, 'stok' => 25],
            ['nama' => 'Voucher Diskon 20%', 'jenis' => 'voucher', 'deskripsi' => 'Diskon 20% untuk seluruh menu, maksimal potongan Rp40.000.', 'poin_dibutuhkan' => 250, 'stok' => null],
            ['nama' => 'Paket Combo Spesial', 'jenis' => 'produk', 'deskripsi' => 'Satu paket combo spesial pilihan barista, gratis penuh.', 'poin_dibutuhkan' => 400, 'stok' => 15],
        ];

        foreach ($rewards as $reward) {
            RewardKatalog::firstOrCreate(
                ['nama' => $reward['nama']],
                $reward + ['status_aktif' => true]
            );
        }
    }
}
