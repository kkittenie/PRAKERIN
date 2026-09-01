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
                'kode_produk' => 'SSH-DT001',
                'nama_produk' => 'Chocolate Glazed Donut',
                'deskripsi' => 'Donat lembut dilapisi glaze cokelat lezat khusus varian bundling.',
                'foto' => 'produk/SSH-DT001/Chocolate-Glazed-Donuts.jpeg',
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
                'kode_produk' => 'SSH-DT002',
                'nama_produk' => 'Strawberry Glazed Donut',
                'deskripsi' => 'Donat segar dengan lapisan glaze stroberi manis dan harum.',
                'foto' => 'produk/SSH-DT002/Strawberry-Glazed-Donuts.jpeg',
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
                'kode_produk' => 'SSH-DT003',
                'nama_produk' => 'Tiramisu Donut',
                'deskripsi' => 'Donat rasa tiramisu dengan taburan bubuk kakao khusus varian bundling.',
                'foto' => 'produk/SSH-DT003/Tiramisu-Donuts.jpeg',
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
                'kode_produk' => 'SSH-DT004',
                'nama_produk' => 'Matcha Green Tea Donut',
                'deskripsi' => 'Donat lezat berlapis glaze matcha green tea otentik khusus bundling.',
                'foto' => 'produk/SSH-DT004/Matcha-Glazed-Donuts.jpeg',
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
                'kode_produk' => 'SSH-DT005',
                'nama_produk' => 'Sugar Glazed Donut',
                'deskripsi' => 'Donat klasik dengan lapisan glaze gula manis khusus varian bundling.',
                'foto' => 'produk/SSH-DT005/Sugar-Glazed-Donuts.jpeg',
                'harga_jual' => 0,
                'harga_modal' => 5000.00,
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