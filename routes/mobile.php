<?php

use App\Http\Controllers\Mobile\AkunController;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\BundlingController;
use App\Http\Controllers\Mobile\PromoEventController;
use App\Http\Controllers\Mobile\PromoStatusController;
use App\Http\Controllers\Mobile\CheckoutController;
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\GoogleAuthController;
use App\Http\Controllers\Mobile\KeranjangController;
use App\Http\Controllers\Mobile\KontenController;
use App\Http\Controllers\Mobile\MenuController;
use App\Http\Controllers\Mobile\NotificationController;
use App\Http\Controllers\Mobile\PesananController;
use App\Http\Controllers\Mobile\ProdukController;
use App\Http\Controllers\Mobile\RewardController;
use App\Http\Controllers\Mobile\RiwayatController;
use App\Http\Controllers\Mobile\SettingsController;
use App\Http\Controllers\Mobile\StampController;
use Illuminate\Support\Facades\Route;

    // Route auth yang diakses tanpa prefix mobile/ (jadi /welcome, /login, /keluar)
    Route::name('member.')->group(function () {
        Route::get('/welcome', [AuthController::class, 'welcome'])->name('welcome');
        Route::match(['get', 'post'], '/keluar', [AuthController::class, 'logout'])->name('logout');

        // Login pelanggan (login admin ada di /admin/login, lihat routes/auth.php)
        Route::get('/login', [AuthController::class, 'tampilkanLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.store');

        // URL lama /masuk tetap jalan, diarahkan ke /login
        Route::redirect('/masuk', '/login');

        // Login / daftar dengan Google (khusus pelanggan)
        Route::middleware('guest:member')->group(function () {
            Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
            Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

            // Akun Google belum terdaftar -> lengkapi data dulu (alur ala GitHub)
            Route::get('/auth/google/daftar', [GoogleAuthController::class, 'tampilkanRegister'])->name('google.register');
            Route::post('/auth/google/daftar', [GoogleAuthController::class, 'register'])->name('google.register.store');
            Route::get('/auth/google/cek-username', [GoogleAuthController::class, 'cekUsername'])->name('google.username');
            Route::get('/auth/google/batal', [GoogleAuthController::class, 'batal'])->name('google.batal');
        });
    });

    Route::prefix('mobile')->name('member.')->group(function () {

        Route::get('/', function () {
            return redirect()->route('member.welcome');
        });

    Route::get('/beranda', [DashboardController::class, 'index'])->name('beranda');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    Route::get('/promo-status', [PromoStatusController::class, 'index'])->name('promo-status');
    Route::get('/akun', [AkunController::class, 'index'])->name('akun');
    Route::get('/info/{slug}', [KontenController::class, 'show'])->name('konten.show');

    Route::get('/produk/{produk}', [ProdukController::class, 'show'])->name('produk.show');
    Route::get('/bundling/{bundling}', [BundlingController::class, 'show'])->name('bundling.show');
    Route::get('/promo-event/{promoEvent}', [PromoEventController::class, 'show'])->name('promo-event.show');

    Route::middleware('guest:member')->group(function () {
            Route::get('/daftar', [AuthController::class, 'tampilkanRegister'])->name('register');
            Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
            Route::post('/kirim-otp', [AuthController::class, 'kirimOtp'])->name('otp.send');
            Route::post('/verifikasi-otp', [AuthController::class, 'verifikasiOtp'])->name('otp.verify');
        });

    Route::middleware('auth:member')->group(function () {
        Route::get('/profil', [AkunController::class, 'profil'])->name('profil');
        Route::post('/profil', [AkunController::class, 'updateProfil'])->name('profil.update');
        Route::delete('/profil', [AkunController::class, 'hapusAkun'])->name('profil.hapus-akun');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/ubah-password', [SettingsController::class, 'ubahPassword'])->name('settings.ubah-password');
        Route::post('/settings/verifikasi-password-lama', [SettingsController::class, 'verifikasiPasswordLama'])->name('settings.verifikasi-password-lama');
        Route::post('/settings/ubah-password', [SettingsController::class, 'updatePassword'])->name('settings.ubah-password.update');

        Route::get('/reward', [RewardController::class, 'index'])->name('reward.index');
        Route::get('/reward/{reward}', [RewardController::class, 'show'])->name('reward.show');
        Route::post('/reward/{reward}/tukar', [RewardController::class, 'redeem'])->name('reward.tukar');

        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');

        Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
        Route::get('/notifikasi/unread-count', [NotificationController::class, 'unreadCount'])->name('notifikasi.unread-count');
        Route::post('/notifikasi/{notification}/baca', [NotificationController::class, 'read'])->name('notifikasi.baca');
        Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifikasi.baca-semua');
        Route::post('/notifikasi/token', [NotificationController::class, 'daftarkanToken'])->name('notifikasi.token');

        Route::get('/stamps', [StampController::class, 'index'])->name('stamps.index');
        Route::post('/stamps/redeem', [StampController::class, 'redeem'])->name('stamps.redeem');

        Route::post('/produk/{produk}/keranjang', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
        Route::post('/bundling/{bundling}/keranjang', [KeranjangController::class, 'tambahBundling'])->name('keranjang.tambah-bundling');
        Route::post('/promo-event/{promoEvent}/keranjang', [KeranjangController::class, 'tambahPromoEvent'])->name('keranjang.tambah-promo-event');
        Route::patch('/keranjang/{itemId}', [KeranjangController::class, 'ubahJumlah'])->name('keranjang.ubah-jumlah');
        Route::patch('/keranjang/{itemId}/perbarui', [KeranjangController::class, 'perbarui'])->name('keranjang.perbarui');
        Route::patch('/keranjang/{itemId}/perbarui-bundling', [KeranjangController::class, 'perbaruiBundling'])->name('keranjang.perbarui-bundling');
        Route::patch('/keranjang/{itemId}/perbarui-promo-event', [KeranjangController::class, 'perbaruiPromoEvent'])->name('keranjang.perbarui-promo-event');
        Route::delete('/keranjang/{itemId}', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout/voucher', [CheckoutController::class, 'pilihVoucher'])->name('checkout.voucher');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('/pesanan/{transaksi}', [PesananController::class, 'show'])->name('pesanan.show');
        Route::get('/pesanan/{transaksi}/status', [PesananController::class, 'status'])->name('pesanan.status');
        Route::get('/pesanan/{transaksi}/cara-pembayaran', [PesananController::class, 'caraPembayaran'])->name('pesanan.cara-pembayaran');
        Route::post('/pesanan/{transaksi}/bukti-pembayaran', [PesananController::class, 'uploadBukti'])->name('pesanan.upload-bukti');
        Route::post('/pesanan/{transaksi}/konfirmasi-bukti', [PesananController::class, 'konfirmasiBukti'])->name('pesanan.konfirmasi-bukti');
    });
});