<?php

namespace Database\Seeders;

use App\Models\DetailProduk;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

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

        $sourceDir = base_path('produk-transparan');

        $products = [
            // ==========================================
            // 1. Celebration Cakes
            // ==========================================
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC001',
                'nama_produk' => 'Classic Birthday Cake',
                'deskripsi' => 'Our signature vintage heart celebration cake made of soft vanilla chiffon layered with whipped chantilly cream and delicate pink floral piping.',
                'harga_jual' => 285000.00,
                'harga_modal' => 142000.00,
                'stok' => 8,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'vintage-strawberry-cake copy.png',
                'filename' => 'classic-birthday-cake.png',
            ],
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC002',
                'nama_produk' => 'Chocolate Ganache Celebration Cake',
                'deskripsi' => 'Rich moist dark chocolate sponge layered with silky ganache, finished with a glossy chocolate drip and fresh ripe strawberries.',
                'harga_jual' => 325000.00,
                'harga_modal' => 160000.00,
                'stok' => 6,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1758.png',
                'filename' => 'chocolate-ganache-celebration-cake.png',
            ],
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC003',
                'nama_produk' => 'Strawberry Chiffon Floral Cake',
                'deskripsi' => 'Airy vanilla chiffon sponge coated in light snow cream, adorned with fresh strawberries and hand-piped blossom rosettes.',
                'harga_jual' => 295000.00,
                'harga_modal' => 145000.00,
                'stok' => 5,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1754.png',
                'filename' => 'strawberry-chiffon-floral-cake.png',
            ],
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC004',
                'nama_produk' => 'Berry Charlotte Heart Cake',
                'deskripsi' => 'Romantic heart-shaped Charlotte cake bordered with crisp ladyfinger biscuits, tied with a crimson satin bow, and filled with fresh raspberries.',
                'harga_jual' => 345000.00,
                'harga_modal' => 170000.00,
                'stok' => 5,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1755.png',
                'filename' => 'berry-charlotte-heart-cake.png',
            ],
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC005',
                'nama_produk' => 'Cherry Blossom Birthday Cake',
                'deskripsi' => 'Delightful pastel blush celebration cake layered with sweet cream and topped with fresh glossy cherries and vintage scallop borders.',
                'harga_jual' => 310000.00,
                'harga_modal' => 155000.00,
                'stok' => 6,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1760.png',
                'filename' => 'cherry-blossom-birthday-cake.png',
            ],
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC006',
                'nama_produk' => 'Tiramisu Celebration Cake',
                'deskripsi' => 'Authentic Italian espresso-soaked savoiardi sponge layered with velvety mascarpone cheese and dusted with rich Dutch dark cocoa.',
                'harga_jual' => 365000.00,
                'harga_modal' => 180000.00,
                'stok' => 4,
                'stok_minimum' => 1,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1756.png',
                'filename' => 'tiramisu-celebration-cake.png',
            ],
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC007',
                'nama_produk' => 'Mini Birthday Cake',
                'deskripsi' => 'Petite 10 cm lunchbox cake with blush strawberry cream, a fresh red cherry on top, and a crisp artisan butter cookie.',
                'harga_jual' => 95000.00,
                'harga_modal' => 47000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1761.png',
                'filename' => 'mini-birthday-cake.png',
            ],
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC008',
                'nama_produk' => 'Vintage White Lettering Cake',
                'deskripsi' => 'Minimalist Korean aesthetic white cake finished with delicate French shell piping and bespoke scripted message space.',
                'harga_jual' => 275000.00,
                'harga_modal' => 135000.00,
                'stok' => 5,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1772.png',
                'filename' => 'vintage-white-lettering-cake.png',
            ],
            [
                'kategori_id' => $categoryModels['Celebration Cakes']->id,
                'kode_produk' => 'SSH-CC009',
                'nama_produk' => 'Daisy Bunny Sculpted Cake',
                'deskripsi' => 'Handcrafted 3D sculpted rabbit cake layered with soft vanilla sponge, fluffy buttercream texture, and blossom accents.',
                'harga_jual' => 380000.00,
                'harga_modal' => 190000.00,
                'stok' => 4,
                'stok_minimum' => 1,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1773.png',
                'filename' => 'daisy-bunny-sculpted-cake.png',
            ],

            // ==========================================
            // 2. Cake Slices & Cupcakes
            // ==========================================
            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS001',
                'nama_produk' => 'Japanese Strawberry Shortcake Slice',
                'deskripsi' => 'Cloud-like Japanese chiffon sponge layered with ripe fresh strawberries and feather-light Hokkaido whipped cream.',
                'harga_jual' => 38000.00,
                'harga_modal' => 18000.00,
                'stok' => 15,
                'stok_minimum' => 4,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'promo-cake-1 copy.png',
                'filename' => 'japanese-strawberry-shortcake-slice.png',
            ],
            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS002',
                'nama_produk' => 'Pink Velvet Strawberry Slice',
                'deskripsi' => 'Tender blush velvet cake layered with light cream cheese frosting and a whole strawberry accent.',
                'harga_jual' => 36000.00,
                'harga_modal' => 17000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1765.png',
                'filename' => 'pink-velvet-strawberry-slice.png',
            ],
            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS003',
                'nama_produk' => 'Blueberry Chiffon Slice',
                'deskripsi' => 'Delicate sponge infused with wild blueberry coulis, layered with lavender cream and crowned with fresh blueberries.',
                'harga_jual' => 36000.00,
                'harga_modal' => 17000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1764.png',
                'filename' => 'blueberry-chiffon-slice.png',
            ],
            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS004',
                'nama_produk' => 'Classic Tiramisu Dessert Slice',
                'deskripsi' => 'Portion of rich mascarpone cream and espresso ladyfingers, generously dusted with dark cocoa and topped with a fresh strawberry.',
                'harga_jual' => 42000.00,
                'harga_modal' => 20000.00,
                'stok' => 10,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1769.png',
                'filename' => 'classic-tiramisu-dessert-slice.png',
            ],
            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS005',
                'nama_produk' => 'Vanilla Buttercream Cupcake',
                'deskripsi' => 'Moist golden vanilla cupcake topped with swirl of blush pink buttercream and a fresh farm strawberry.',
                'harga_jual' => 22000.00,
                'harga_modal' => 11000.00,
                'stok' => 24,
                'stok_minimum' => 6,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1762.png',
                'filename' => 'vanilla-buttercream-cupcake.png',
            ],
            [
                'kategori_id' => $categoryModels['Cake Slices & Cupcakes']->id,
                'kode_produk' => 'SSH-CS006',
                'nama_produk' => 'Blueberry Lavender Cupcake',
                'deskripsi' => 'Tender cupcake with wild blueberry filling, crowned with silky purple swirl frosting and fresh blueberries.',
                'harga_jual' => 24000.00,
                'harga_modal' => 12000.00,
                'stok' => 20,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1763.png',
                'filename' => 'blueberry-lavender-cupcake.png',
            ],

            // ==========================================
            // 3. Pastry & Croissant
            // ==========================================
            [
                'kategori_id' => $categoryModels['Pastry & Croissant']->id,
                'kode_produk' => 'SSH-PS001',
                'nama_produk' => 'Strawberry Cream Artisan Croissant',
                'deskripsi' => 'Extra-flaky French butter croissant baked golden, sliced open and filled with sweet whipped vanilla chantilly and ripe strawberries.',
                'harga_jual' => 32000.00,
                'harga_modal' => 15000.00,
                'stok' => 16,
                'stok_minimum' => 4,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'promo-croissant copy.png',
                'filename' => 'strawberry-cream-artisan-croissant.png',
            ],
            [
                'kategori_id' => $categoryModels['Pastry & Croissant']->id,
                'kode_produk' => 'SSH-PS002',
                'nama_produk' => 'Caramel Croffle Heart',
                'deskripsi' => 'Crispy caramelized French croissant pressed into a heart waffle, served with rich vanilla gelato and warm sea-salt caramel.',
                'harga_jual' => 28000.00,
                'harga_modal' => 13000.00,
                'stok' => 14,
                'stok_minimum' => 4,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1757.png',
                'filename' => 'caramel-croffle-heart.png',
            ],
            [
                'kategori_id' => $categoryModels['Pastry & Croissant']->id,
                'kode_produk' => 'SSH-PS003',
                'nama_produk' => 'Fluffy Souffle Pancake Stack',
                'deskripsi' => 'Triple stack of melt-in-mouth Japanese souffle pancakes, topped with light cream, fresh strawberries, and pure maple syrup.',
                'harga_jual' => 45000.00,
                'harga_modal' => 22000.00,
                'stok' => 10,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1771.png',
                'filename' => 'fluffy-souffle-pancake-stack.png',
            ],
            [
                'kategori_id' => $categoryModels['Pastry & Croissant']->id,
                'kode_produk' => 'SSH-PS004',
                'nama_produk' => 'Artisan Butter Bear Cookies',
                'deskripsi' => 'Pouch of handcrafted French butter shortbread cookies with cocoa and vanilla silhouettes, baked crisp and fragrant.',
                'harga_jual' => 25000.00,
                'harga_modal' => 11000.00,
                'stok' => 25,
                'stok_minimum' => 5,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1770.png',
                'filename' => 'artisan-butter-bear-cookies.png',
            ],

            // ==========================================
            // 4. Tart & Danish
            // ==========================================
            [
                'kategori_id' => $categoryModels['Tart & Danish']->id,
                'kode_produk' => 'SSH-TD001',
                'nama_produk' => 'Strawberry Macaron Tartlet',
                'deskripsi' => 'Giant crisp French macaron shell filled with silky white chocolate ganache, fresh strawberries, and strawberry coulis.',
                'harga_jual' => 32000.00,
                'harga_modal' => 15000.00,
                'stok' => 12,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1766.png',
                'filename' => 'strawberry-macaron-tartlet.png',
            ],
            [
                'kategori_id' => $categoryModels['Tart & Danish']->id,
                'kode_produk' => 'SSH-TD002',
                'nama_produk' => 'Assorted French Macarons Basket',
                'deskripsi' => 'Handwoven basket of assorted Parisian macarons in vanilla bean, salted caramel, rose blossom, and chocolate.',
                'harga_jual' => 55000.00,
                'harga_modal' => 26000.00,
                'stok' => 8,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1767.png',
                'filename' => 'assorted-french-macarons-basket.png',
            ],
            [
                'kategori_id' => $categoryModels['Tart & Danish']->id,
                'kode_produk' => 'SSH-TD003',
                'nama_produk' => 'Pastel Spring Macaron Collection',
                'deskripsi' => 'Seasonal pastel macarons featuring crisp almond shells with matcha, pistachio, and raspberry ganache fillings.',
                'harga_jual' => 55000.00,
                'harga_modal' => 26000.00,
                'stok' => 8,
                'stok_minimum' => 2,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1768.png',
                'filename' => 'pastel-spring-macaron-collection.png',
            ],
            [
                'kategori_id' => $categoryModels['Tart & Danish']->id,
                'kode_produk' => 'SSH-TD004',
                'nama_produk' => 'Wild Blueberry Cheese Tart',
                'deskripsi' => 'Artisanal sweet shortcrust tart baked with Hokkaido cream cheese and covered with glossy glazed wild blueberries.',
                'harga_jual' => 35000.00,
                'harga_modal' => 17000.00,
                'stok' => 10,
                'stok_minimum' => 3,
                'status_aktif' => true,
                'status_ketersediaan' => 'stok_ada',
                'source_image' => 'IMG_1759.png',
                'filename' => 'wild-blueberry-cheese-tart.png',
            ],
        ];

        foreach ($products as $prod) {
            $destPath = null;

            if (!empty($prod['source_image']) && !empty($prod['filename'])) {
                $sourceFile = $sourceDir . '/' . $prod['source_image'];
                $destFolder = 'produk/' . $prod['kode_produk'];
                $destPath = $destFolder . '/' . $prod['filename'];

                if (file_exists($sourceFile)) {
                    Storage::disk('public')->makeDirectory($destFolder);
                    Storage::disk('public')->put($destPath, file_get_contents($sourceFile));
                }
            }

            $prodData = Arr::only($prod, [
                'kategori_id',
                'nama_produk',
                'deskripsi',
                'status_aktif',
            ]);
            $prodData['foto'] = $destPath;
            $prodData['dijual_satuan'] = $prod['dijual_satuan'] ?? true;

            $detail = DetailProduk::where('kode_produk', $prod['kode_produk'])->first();
            $produk = $detail?->produk ?? new Produk();

            $produk->fill($prodData)->save();

            DetailProduk::updateOrCreate(
                ['kode_produk' => $prod['kode_produk']],
                [
                    'produk_id' => $produk->id,
                    'harga_modal' => $prod['harga_modal'] ?? 0,
                    'harga_jual' => $prod['harga_jual'] ?? 0,
                    'stok' => $prod['stok'] ?? 0,
                    'stok_minimum' => $prod['stok_minimum'] ?? 0,
                    'foto' => $destPath,
                    'status_ketersediaan' => $prod['status_ketersediaan'] ?? 'stok_ada',
                    'status_aktif' => true,
                    'urutan' => 0,
                ]
            );
        }
    }
}
