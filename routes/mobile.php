<?php

use App\Http\Controllers\Mobile\AkunController;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\BundlingController;
use App\Http\Controllers\Mobile\CheckoutController;
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\KeranjangController;
use App\Http\Controllers\Mobile\KontenController;
use App\Http\Controllers\Mobile\MenuController;
use App\Http\Controllers\Mobile\PesananController;
use App\Http\Controllers\Mobile\ProdukController;
use App\Http\Controllers\Mobile\RewardController;
use App\Http\Controllers\Mobile\RiwayatController;
use App\Http\Controllers\Mobile\SettingsController;
use App\Http\Controllers\Mobile\StampController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->name('member.')->group(function () {

    Route::get('/', function () {
        return redirect()->route('member.welcome');
    });

    Route::get('/welcome', [AuthController::class, 'welcome'])->name('welcome');

    Route::get('/beranda', [DashboardController::class, 'index'])->name('beranda');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    Route::get('/akun', [AkunController::class, 'index'])->name('akun');
    Route::get('/info/{slug}', [KontenController::class, 'show'])->name('konten.show');

    Route::get('/produk/{produk}', [ProdukController::class, 'show'])->name('produk.show');
    Route::get('/bundling/{bundling}', [BundlingController::class, 'show'])->name('bundling.show');

    Route::match(['get', 'post'], '/keluar', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('guest:member')->group(function () {
        Route::get('/masuk', [AuthController::class, 'tampilkanLogin'])->name('login');
        Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
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

        Route::get('/stamps', [StampController::class, 'index'])->name('stamps.index');
        Route::post('/stamps/redeem', [StampController::class, 'redeem'])->name('stamps.redeem');

        Route::post('/produk/{produk}/keranjang', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
        Route::post('/bundling/{bundling}/keranjang', [KeranjangController::class, 'tambahBundling'])->name('keranjang.tambah-bundling');
        Route::patch('/keranjang/{itemId}', [KeranjangController::class, 'ubahJumlah'])->name('keranjang.ubah-jumlah');
        Route::patch('/keranjang/{itemId}/perbarui', [KeranjangController::class, 'perbarui'])->name('keranjang.perbarui');
        Route::patch('/keranjang/{itemId}/perbarui-bundling', [KeranjangController::class, 'perbaruiBundling'])->name('keranjang.perbarui-bundling');
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