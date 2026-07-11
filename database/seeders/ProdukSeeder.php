<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Categories
        $categories = [
            'Croissant & Danish',
            'Cake & Tart',
            'Bread & Bun',
            'Cookies & Sweet Treats',
        ];

        $categoryModels = [];
        foreach ($categories as $catName) {
            $categoryModels[$catName] = KategoriProduk::firstOrCreate([
                'nama_kategori' => $catName,
            ]);
        }

        // 2. Seed Products
        $products = [
            // Croissant & Danish
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'PST-CR001',
                'nama_produk' => 'Butter Croissant',
                'harga_jual' => 18000.00,
                'harga_modal' => 9000.00,
                'stok' => 20,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'PST-CR002',
                'nama_produk' => 'Chocolate Croissant (Pain au Chocolat)',
                'harga_jual' => 22000.00,
                'harga_modal' => 11000.00,
                'stok' => 15,
                'stok_minimum' => 4,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'PST-CR003',
                'nama_produk' => 'Almond Croissant',
                'harga_jual' => 26000.00,
                'harga_modal' => 13000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'PST-DN004',
                'nama_produk' => 'Blueberry Danish',
                'harga_jual' => 24000.00,
                'harga_modal' => 12000.00,
                'stok' => 10,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],

            // Cake & Tart
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'PST-CK001',
                'nama_produk' => 'Strawberry Cheese Tart',
                'harga_jual' => 28000.00,
                'harga_modal' => 14000.00,
                'stok' => 8,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'PST-CK002',
                'nama_produk' => 'Chocolate Fudge Cake Slice',
                'harga_jual' => 35000.00,
                'harga_modal' => 17000.00,
                'stok' => 6,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],

            // Bread & Bun
            [
                'kategori_id' => $categoryModels['Bread & Bun']->id,
                'kode_produk' => 'PST-BR001',
                'nama_produk' => 'Cinnamon Roll',
                'harga_jual' => 20000.00,
                'harga_modal' => 10000.00,
                'stok' => 15,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],
            [
                'kategori_id' => $categoryModels['Bread & Bun']->id,
                'kode_produk' => 'PST-BR002',
                'nama_produk' => 'Soft Milk Bun',
                'harga_jual' => 15000.00,
                'harga_modal' => 7500.00,
                'stok' => 25,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],

            // Cookies & Sweet Treats
            [
                'kategori_id' => $categoryModels['Cookies & Sweet Treats']->id,
                'kode_produk' => 'PST-SW001',
                'nama_produk' => 'Chocolate Chip Cookie',
                'harga_jual' => 12000.00,
                'harga_modal' => 6000.00,
                'stok' => 30,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
            ],
            [
                'kategori_id' => $categoryModels['Cookies & Sweet Treats']->id,
                'kode_produk' => 'PST-SW002',
                'nama_produk' => 'Macaron Assorted (Box of 6)',
                'harga_jual' => 75000.00,
                'harga_modal' => 38000.00,
                'stok' => 10,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
            // 8 Produk Pre-Order (Pesan Dulu)
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'PST-PO001',
                'nama_produk' => 'Custom Birthday Cake (Diameter 16cm)',
                'harga_jual' => 250000.00,
                'harga_modal' => 120000.00,
                'stok' => 5,
                'stok_minimum' => 0,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'PST-PO002',
                'nama_produk' => 'Black Forest Cake (Whole)',
                'harga_jual' => 300000.00,
                'harga_modal' => 150000.00,
                'stok' => 3,
                'stok_minimum' => 0,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'PST-PO003',
                'nama_produk' => 'Red Velvet Cake (Whole)',
                'harga_jual' => 320000.00,
                'harga_modal' => 160000.00,
                'stok' => 3,
                'stok_minimum' => 0,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'PST-PO004',
                'nama_produk' => 'Tiramisu Cake (Whole)',
                'harga_jual' => 280000.00,
                'harga_modal' => 140000.00,
                'stok' => 4,
                'stok_minimum' => 0,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'PST-PO005',
                'nama_produk' => 'Fruit Tartlet Party Size (Large)',
                'harga_jual' => 180000.00,
                'harga_modal' => 90000.00,
                'stok' => 5,
                'stok_minimum' => 0,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'PST-PO006',
                'nama_produk' => 'Croissant Party Platter (Pack of 12)',
                'harga_jual' => 190000.00,
                'harga_modal' => 95000.00,
                'stok' => 8,
                'stok_minimum' => 0,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
            [
                'kategori_id' => $categoryModels['Bread & Bun']->id,
                'kode_produk' => 'PST-PO007',
                'nama_produk' => 'Sourdough Boule (Large Size)',
                'harga_jual' => 45000.00,
                'harga_modal' => 20000.00,
                'stok' => 10,
                'stok_minimum' => 0,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
            [
                'kategori_id' => $categoryModels['Cookies & Sweet Treats']->id,
                'kode_produk' => 'PST-PO008',
                'nama_produk' => 'Macaron Premium Tower (36 Pcs)',
                'harga_jual' => 380000.00,
                'harga_modal' => 180000.00,
                'stok' => 2,
                'stok_minimum' => 0,
                'status_aktif' => true,
                'status_ketersediaan' => 'pesan_dulu',
            ],
        ];

        foreach ($products as $prod) {
            Produk::updateOrCreate(
                ['kode_produk' => $prod['kode_produk']],
                $prod
            );
        }
    }
}
