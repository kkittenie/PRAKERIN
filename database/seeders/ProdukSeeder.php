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
            'Celebration Cakes',
            'Cake Slices & Cupcakes',
            'Pastry & Croissant',
            'Tart & Danish',
        ];

        $categoryModels = [];
        foreach ($categories as $catName) {
            $categoryModels[$catName] = KategoriProduk::firstOrCreate([
                'nama_kategori' => $catName,
            ]);
        }

        $products = [
            // Celebration Cakes
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC001',
                'nama_produk' => 'Classic Birthday Cake',
                'deskripsi' => 'A classic birthday cake made of soft vanilla sponge layered with whipped cream and fresh strawberries, finished with a smooth cream coating and a space for your personalized message.',
                'harga_jual' => 285000.00,
                'harga_modal' => 142000.00,
                'stok' => 6,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CC001/classic-birthday-cake.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC002',
                'nama_produk' => 'Chocolate Ganache Celebration Cake',
                'deskripsi' => 'Rich and moist chocolate sponge layered with silky dark chocolate ganache and covered with a glossy ganache glaze. A decadent centerpiece for birthdays and special gatherings.',
                'harga_jual' => 325000.00,
                'harga_modal' => 160000.00,
                'stok' => 5,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CC002/chocolate-ganache-celebration-cake.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC003',
                'nama_produk' => 'Red Velvet Cream Cheese Cake',
                'deskripsi' => 'Velvety red cocoa sponge with a subtle tang, layered with smooth cream cheese frosting. A striking cake that is perfect for anniversaries, birthdays, and celebrations.',
                'harga_jual' => 350000.00,
                'harga_modal' => 175000.00,
                'stok' => 5,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CC003/red-velvet-cream-cheese-cake.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC004',
                'nama_produk' => 'Rainbow Party Cake',
                'deskripsi' => 'A cheerful multi-layered rainbow sponge with vanilla buttercream and colorful sprinkles. Fun, bright, and made to bring smiles to every birthday party.',
                'harga_jual' => 310000.00,
                'harga_modal' => 155000.00,
                'stok' => 4,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CC004/rainbow-party-cake.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC005',
                'nama_produk' => 'Tiramisu Celebration Cake',
                'deskripsi' => 'Espresso-soaked sponge layered with light mascarpone cream and finished with a dusting of cocoa powder. An elegant cake with a balanced coffee flavor for grown-up celebrations.',
                'harga_jual' => 375000.00,
                'harga_modal' => 187000.00,
                'stok' => 4,
                'stok_minimum' => 1,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CC005/tiramisu-celebration-cake.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC006',
                'nama_produk' => 'Mini Birthday Cake',
                'deskripsi' => 'A petite 10 cm birthday cake with soft vanilla sponge and whipped cream frosting, ideal for intimate celebrations, small surprises, or a thoughtful gift for one.',
                'harga_jual' => 95000.00,
                'harga_modal' => 47000.00,
                'stok' => 10,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CC006/mini-birthday-cake.jpeg',
            ],

            // Cake Slices & Cupcakes
            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS001',
                'nama_produk' => 'Chocolate Fudge Cake Slice',
                'deskripsi' => 'A generous slice of moist chocolate fudge cake with deep, rich cocoa flavor and a smooth chocolate frosting.',
                'harga_jual' => 35000.00,
                'harga_modal' => 17000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CS001/chocolate-fudge-cake-slice.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS002',
                'nama_produk' => 'Red Velvet Cake Slice',
                'deskripsi' => 'A soft slice of red velvet cake layered with creamy cheese frosting, slightly tangy and perfectly sweet.',
                'harga_jual' => 38000.00,
                'harga_modal' => 19000.00,
                'stok' => 10,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CS002/red-velvet-cake-slice.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS003',
                'nama_produk' => 'Vanilla Buttercream Cupcake',
                'deskripsi' => 'A fluffy vanilla cupcake topped with swirls of silky buttercream and colorful sprinkles. A sweet little treat for any occasion.',
                'harga_jual' => 22000.00,
                'harga_modal' => 11000.00,
                'stok' => 24,
                'stok_minimum' => 6,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CS003/vanilla-buttercream-cupcake.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS004',
                'nama_produk' => 'Chocolate Cupcake',
                'deskripsi' => 'A moist chocolate cupcake crowned with creamy chocolate frosting, made for true chocolate lovers.',
                'harga_jual' => 22000.00,
                'harga_modal' => 11000.00,
                'stok' => 24,
                'stok_minimum' => 6,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-CS004/chocolate-cupcake.jpeg',
            ],

            // Pastry & Croissant
            [
                'kategori_id' => $categoryModels['Pastry & Croissant']->id,
                'kode_produk' => 'SSH-PS001',
                'nama_produk' => 'Butter Croissant',
                'deskripsi' => 'A classic croissant with golden, flaky layers on the outside and a soft, airy crumb inside, baked with real butter.',
                'harga_jual' => 18000.00,
                'harga_modal' => 9000.00,
                'stok' => 20,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-PS001/butter-croissant.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Pastry & Croissant']->id,
                'kode_produk' => 'SSH-PS002',
                'nama_produk' => 'Chocolate Croissant (Pain au Chocolat)',
                'deskripsi' => 'Flaky laminated pastry with a crisp exterior and tender inside, filled with melting dark chocolate.',
                'harga_jual' => 22000.00,
                'harga_modal' => 11000.00,
                'stok' => 15,
                'stok_minimum' => 4,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-PS002/chocolate-croissant.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Pastry & Croissant']->id,
                'kode_produk' => 'SSH-PS003',
                'nama_produk' => 'Almond Croissant',
                'deskripsi' => 'A buttery croissant filled with sweet almond cream and topped with sliced almonds and a light dusting of powdered sugar.',
                'harga_jual' => 26000.00,
                'harga_modal' => 13000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-PS003/almond-croissant.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Pastry & Croissant']->id,
                'kode_produk' => 'SSH-PS004',
                'nama_produk' => 'Cinnamon Roll',
                'deskripsi' => 'A soft, swirled pastry roll with fragrant cinnamon and sugar filling, topped with sweet cream cheese glaze.',
                'harga_jual' => 20000.00,
                'harga_modal' => 10000.00,
                'stok' => 15,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-PS004/cinnamon-roll.jpeg',
            ],

            // Tart & Danish
            [
                'kategori_id' => $categoryModels['Tart & Danish']->id,
                'kode_produk' => 'SSH-TD001',
                'nama_produk' => 'Strawberry Cheese Tart',
                'deskripsi' => 'A buttery tart shell filled with smooth cream cheese and topped with fresh strawberries and a light glaze.',
                'harga_jual' => 28000.00,
                'harga_modal' => 14000.00,
                'stok' => 10,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-TD001/strawberry-cheese-tart.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Tart & Danish']->id,
                'kode_produk' => 'SSH-TD002',
                'nama_produk' => 'Blueberry Danish',
                'deskripsi' => 'A flaky, buttery danish pastry filled with vanilla custard and topped with sweet-tart blueberry compote.',
                'harga_jual' => 24000.00,
                'harga_modal' => 12000.00,
                'stok' => 10,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-TD002/blueberry-danish.jpeg',
            ],

            [
                'kategori_id' => $categoryModels['Tart & Danish']->id,
                'kode_produk' => 'SSH-TD003',
                'nama_produk' => 'Fruit Custard Tart',
                'deskripsi' => 'A crisp tart shell filled with silky vanilla custard and crowned with an assortment of fresh seasonal fruits.',
                'harga_jual' => 30000.00,
                'harga_modal' => 15000.00,
                'stok' => 8,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'foto' => 'produk/SSH-TD003/fruit-custard-tart.jpeg',
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