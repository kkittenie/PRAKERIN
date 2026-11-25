<?php

namespace Database\Seeders;

use App\Models\KontenStatis;
use Illuminate\Database\Seeder;

class KontenStatisSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'slug'  => 'help-center',
                'judul' => 'Help Center',
                'konten' => '<h2>Pusat Bantuan Daisy Bakery</h2>
<p>Selamat datang di Pusat Bantuan Daisy Bakery. Kami siap membantu Anda terkait pesanan, akun member, serta program reward &amp; stamp.</p>

<h3>Pertanyaan Umum (FAQ)</h3>

<p><strong>1. Bagaimana cara memesan produk di Daisy Bakery?</strong><br>
Anda dapat memilih menu favorit melalui aplikasi member, memasukkan produk ke keranjang, dan melakukan checkout untuk pesanan pickup atau antar.</p>

<p><strong>2. Bagaimana cara mengumpulkan Poin &amp; Stamp?</strong><br>
Setiap kali Anda bertransaksi, poin dan stamp akan otomatis ditambahkan ke akun member Anda sesuai ketentuan program reward yang berlaku.</p>

<p><strong>3. Bagaimana cara menghubungi Customer Service?</strong><br>
Anda dapat menghubungi layanan pelanggan kami melalui WhatsApp Official Daisy Customer Service di tombol yang tersedia pada halaman Akun.</p>',
            ],
            [
                'slug'  => 'terms-conditions',
                'judul' => 'Terms and Conditions',
                'konten' => '<h2>Syarat dan Ketentuan Daisy Bakery</h2>
<p>Dengan mengakses atau menggunakan aplikasi Daisy Bakery, Anda menyetujui untuk terikat oleh Syarat dan Ketentuan berikut.</p>

<h3>1. Penggunaan Akun Member</h3>
<p>Akun member bersifat pribadi dan tidak dapat dipindahtangankan. Anda bertanggung jawab untuk menjaga kerahasiaan informasi akun dan password Anda.</p>

<h3>2. Pemesanan dan Pembayaran</h3>
<p>Semua pesanan yang telah dikonfirmasi dan dibayar akan diproses sesuai waktu estimasi. Penolakan atau pembatalan pesanan mengacu pada kebijakan toko yang berlaku.</p>

<h3>3. Program Poin dan Reward</h3>
<p>Poin dan Stamp yang dikumpulkan tidak dapat diuangkan dan hanya dapat ditukarkan dengan produk atau voucher yang tersedia pada katalog reward Daisy Bakery.</p>',
            ],
            [
                'slug'  => 'privacy-policy',
                'judul' => 'Privacy Policy',
                'konten' => '<h2>Kebijakan Privasi Daisy Bakery</h2>
<p>Kami di Daisy Bakery berkomitmen untuk melindungi dan menghormati hak privasi pengguna dan pelanggan kami.</p>

<h3>1. Informasi yang Kami Kumpulkan</h3>
<p>Kami mengumpulkan informasi yang Anda berikan saat mendaftar akun, seperti nama, nomor HP, username, dan data transaksi pemesanan.</p>

<h3>2. Penggunaan Informasi</h3>
<p>Informasi Anda digunakan untuk mengelola akun Anda, memproses pesanan, memberikan layanan pelanggan, serta menghitung poin reward dan stamp Anda.</p>

<h3>3. Keamanan Data</h3>
<p>Kami mengimplementasikan langkah-langkah keamanan teknis dan organisasional yang tepat untuk melindungi data pribadi Anda dari akses tidak sah atau kebocoran.</p>',
            ],
        ];

        foreach ($data as $item) {
            KontenStatis::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'judul'  => $item['judul'],
                    'konten' => $item['konten'],
                ]
            );
        }
    }
}
