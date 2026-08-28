<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukKhususBundlingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = KategoriProduk::firstOrCreate([
            'nama_kategori' => 'Donut',
        ]);

        $donuts = [
            [
                'kategori_id' => $kategori->id,
                'kode_produk' => 'SSH-DN001',
                'nama_produk' => 'Chocolate Glazed Donut',
                'deskripsi' => 'Donat lembut dilapisi glaze cokelat lezat khusus varian bundling.',
                'harga_jual' => 0,
                'harga_modal' => 5000.00,
                'stok' => 2,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'is_khusus_bundling' => true,
                'punya_ukuran' => false,
            ],
            [
                'kategori_id' => $kategori->id,
                'kode_produk' => 'SSH-DN002',
                'nama_produk' => 'Strawberry Glazed Donut',
                'deskripsi' => 'Donat segar dengan lapisan glaze stroberi manis dan harum.',
                'harga_jual' => 0,
                'harga_modal' => 5000.00,
                'stok' => 2,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'is_khusus_bundling' => true,
                'punya_ukuran' => false,
            ],
            [
                'kategori_id' => $kategori->id,
                'kode_produk' => 'SSH-DN003',
                'nama_produk' => 'Tiramisu Donut',
                'deskripsi' => 'Donat rasa tiramisu dengan taburan bubuk kakao khusus varian bundling.',
                'harga_jual' => 0,
                'harga_modal' => 6000.00,
                'stok' => 2,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'is_khusus_bundling' => true,
                'punya_ukuran' => false,
            ],
            [
                'kategori_id' => $kategori->id,
                'kode_produk' => 'SSH-DN004',
                'nama_produk' => 'Matcha Green Tea Donut',
                'deskripsi' => 'Donat lezat berlapis glaze matcha green tea otentik khusus bundling.',
                'harga_jual' => 0,
                'harga_modal' => 6000.00,
                'stok' => 2,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'is_khusus_bundling' => true,
                'punya_ukuran' => false,
            ],
        ];

        foreach ($donuts as $donut) {
            Produk::updateOrCreate(
                ['kode_produk' => $donut['kode_produk']],
                $donut
            );
        }
    }
}
