<?php

namespace Database\Seeders;

use App\Models\DetailProduk;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

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
                'deskripsi' => 'Butter Croissant adalah croissant klasik dengan tekstur berlapis yang renyah dan golden brown di bagian luar, namun tetap lembut dan airy di bagian dalam.',
                'harga_jual' => 18000.00,
                'harga_modal' => 9000.00,
                'stok' => 20,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CR001/butter-croissant.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'SSH-CR002',
                'nama_produk' => 'Chocolate Croissant (Pain au Chocolat)',
                'deskripsi' => 'Chocolate Croissant atau Pain au Chocolat merupakan pastry berlapis dengan tekstur renyah di bagian luar dan lembut di bagian dalam, dipadukan dengan isian cokelat yang meleleh dan kaya rasa.',
                'harga_jual' => 22000.00,
                'harga_modal' => 11000.00,
                'stok' => 15,
                'stok_minimum' => 4,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CR002/chocolate-croissant.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'SSH-CR003',
                'nama_produk' => 'Almond Croissant',
                'deskripsi' => 'Almond Croissant menghadirkan perpaduan pastry yang renyah dan buttery dengan cita rasa almond yang lembut dan khas.',
                'harga_jual' => 26000.00,
                'harga_modal' => 13000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CR003/almond-croissant.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Croissant & Danish']->id,
                'kode_produk' => 'SSH-DN004',
                'nama_produk' => 'Blueberry Danish',
                'deskripsi' => 'Blueberry Danish merupakan pastry lembut dan berlapis dengan topping blueberry yang memberikan perpaduan rasa manis dan sedikit asam yang menyegarkan.',
                'harga_jual' => 24000.00,
                'harga_modal' => 12000.00,
                'stok' => 10,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-DN004/blueberry-danish.jpeg',
            ],


                // Cake & Tart
            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'SSH-CK001',
                'nama_produk' => 'Strawberry Cheese Tart',
                'deskripsi' => 'Strawberry Cheese Tart memadukan tekstur tart yang lembut dan creamy dengan rasa cheesecake yang kaya namun tetap ringan.',
                'harga_jual' => 28000.00,
                'harga_modal' => 14000.00,
                'stok' => 8,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CK001/strawberry-cheese-tart.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Cake & Tart']->id,
                'kode_produk' => 'SSH-CK002',
                'nama_produk' => 'Chocolate Fudge Cake Slice',
                'deskripsi' => 'Chocolate Fudge Cake Slice adalah potongan cake cokelat dengan tekstur lembut, moist, dan kaya rasa.',
                'harga_jual' => 35000.00,
                'harga_modal' => 17000.00,
                'stok' => 6,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CK002/chocolate-fudge-cake.jpeg',
            ],


                // Bread & Bun
            [
                'kategori_id' => $categoryModels['Bread & Bun']->id,
                'kode_produk' => 'SSH-BR001',
                'nama_produk' => 'Cinnamon Roll',
                'deskripsi' => 'Cinnamon Roll merupakan roti lembut dengan aroma cinnamon yang harum dan menggugah selera.',
                'harga_jual' => 20000.00,
                'harga_modal' => 10000.00,
                'stok' => 15,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-BR001/cinnamon-roll.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Bread & Bun']->id,
                'kode_produk' => 'SSH-BR002',
                'nama_produk' => 'Soft Milk Bun',
                'deskripsi' => 'Soft Milk Bun adalah roti dengan tekstur yang sangat lembut dan fluffy, dibuat untuk memberikan sensasi ringan dan nyaman di setiap gigitan.',
                'harga_jual' => 15000.00,
                'harga_modal' => 7500.00,
                'stok' => 25,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-BR002/soft-milk-bun.jpeg',
            ],


                // Cookies & Sweet Treats
            [
                'kategori_id' => $categoryModels['Cookies & Sweet Treats']->id,
                'kode_produk' => 'SSH-SW001',
                'nama_produk' => 'Chocolate Chip Cookie',
                'deskripsi' => 'Chocolate Chip Cookie memiliki tekstur yang renyah di bagian luar dengan bagian tengah yang tetap lembut dan sedikit chewy.',
                'harga_jual' => 12000.00,
                'harga_modal' => 6000.00,
                'stok' => 30,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-SW001/chocolate-chip-cookies.jpeg',
            ],
        ];

        foreach ($products as $prod) {
            $detail = DetailProduk::where('kode_produk', $prod['kode_produk'])->first();
            $produk = $detail?->produk ?? new Produk();

            // Tabel produks hanya informasi umum; kode, harga, dan stok masuk ke detail produk.
            $produk->fill(Arr::only($prod, ['kategori_id', 'nama_produk', 'deskripsi', 'foto', 'status_aktif', 'dijual_satuan']))->save();

            DetailProduk::updateOrCreate(
                ['kode_produk' => $prod['kode_produk']],
                [
                    'produk_id' => $produk->id,
                    'harga_modal' => $prod['harga_modal'] ?? 0,
                    'harga_jual' => $prod['harga_jual'] ?? 0,
                    'stok' => $prod['stok'] ?? 0,
                    'stok_minimum' => $prod['stok_minimum'] ?? 0,
                    'status_aktif' => true,
                    'urutan' => 0,
                ]
            );
        }
    }
}