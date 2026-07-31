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

        $products = [
            // Croissant & Danish
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'SSH-CR001',
                'nama_produk' => 'Butter Croissant',
                'harga_jual' => 18000.00,
                'harga_modal' => 9000.00,
                'stok' => 20,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/butter-croissant.jpeg',
            ],
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'SSH-CR002',
                'nama_produk' => 'Chocolate Croissant (Pain au Chocolat)',
                'harga_jual' => 22000.00,
                'harga_modal' => 11000.00,
                'stok' => 15,
                'stok_minimum' => 4,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/chocolate-croissant.jpeg',
            ],
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'SSH-CR003',
                'nama_produk' => 'Almond Croissant',
                'harga_jual' => 26000.00,
                'harga_modal' => 13000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/almond-croissant.jpeg',
            ],
            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'SSH-DN004',
                'nama_produk' => 'Blueberry Danish',
                'harga_jual' => 24000.00,
                'harga_modal' => 12000.00,
                'stok' => 10,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/blueberry-danish.jpeg',
            ],

            // Cake & Tart
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'SSH-CK001',
                'nama_produk' => 'Strawberry Cheese Tart',
                'harga_jual' => 28000.00,
                'harga_modal' => 14000.00,
                'stok' => 8,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/strawberry-cheese-tart.jpeg',
            ],
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'SSH-CK002',
                'nama_produk' => 'Chocolate Fudge Cake Slice',
                'harga_jual' => 35000.00,
                'harga_modal' => 17000.00,
                'stok' => 6,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/chocolate-fudge-cake.jpeg',
            ],

            // Bread & Bun
            [
                'kategori_id' => $categoryModels['Bread & Bun']->id,
                'kode_produk' => 'SSH-BR001',
                'nama_produk' => 'Cinnamon Roll',
                'harga_jual' => 20000.00,
                'harga_modal' => 10000.00,
                'stok' => 15,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/cinnamon-roll.jpeg',
            ],
            [
                'kategori_id' => $categoryModels['Bread & Bun']->id,
                'kode_produk' => 'SSH-BR002',
                'nama_produk' => 'Soft Milk Bun',
                'harga_jual' => 15000.00,
                'harga_modal' => 7500.00,
                'stok' => 25,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/soft-milk-bun.jpeg',
            ],

            // Cookies & Sweet Treats
            [
                'kategori_id' => $categoryModels['Cookies & Sweet Treats']->id,
                'kode_produk' => 'SSH-SW001',
                'nama_produk' => 'Chocolate Chip Cookie',
                'harga_jual' => 12000.00,
                'harga_modal' => 6000.00,
                'stok' => 30,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/chocolate-chip-cookies.jpeg',
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