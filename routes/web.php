<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransaksiKasirController;
use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\MemberController;

Route::middleware(['auth'])->group(function () {

    // Dashboard Utama (Beranda)
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('permission:lihat_dashboard')
        ->name('dashboard');

    // Transaksi Kasir (POS)
    Route::middleware('permission:lihat_transaksi')->group(function () {
        Route::get('/transaksi', [TransaksiKasirController::class, 'indeks'])->name('transaksi.indeks');
        Route::get('/transaksi/daftar-ditahan', [TransaksiKasirController::class, 'ambilDitahan'])->name('transaksi.daftar-ditahan');
    });
    Route::middleware('permission:tambah_transaksi')->group(function () {
        Route::post('/transaksi/tahan', [TransaksiKasirController::class, 'simpanDitahan'])->name('transaksi.tahan');
        Route::post('/transaksi/pulihkan/{id}', [TransaksiKasirController::class, 'pulihkanDitahan'])->name('transaksi.pulihkan');
        Route::delete('/transaksi/hapus-ditahan/{id}', [TransaksiKasirController::class, 'hapusDitahan'])->name('transaksi.hapus-ditahan');
        Route::post('/transaksi/bayar', [TransaksiKasirController::class, 'bayar'])->name('transaksi.bayar');
    });

    // Laporan
    Route::middleware('permission:lihat_laporan')->group(function () {
        Route::get('/histori-transaksi', [LaporanController::class, 'historiTransaksi'])->name('histori-transaksi');
        Route::get('/histori-transaksi/{id}/detail', [LaporanController::class, 'detailTransaksi'])->name('histori-transaksi.detail');
        Route::get('/laporan-penjualan', [LaporanController::class, 'laporanPenjualan'])->name('laporan-penjualan');
    });

    Route::resource('roles', RoleController::class)
        ->middleware('permission:lihat_role|tambah_role|edit_role|hapus_role');

    Route::resource('users', UserController::class)
        ->middleware('permission:lihat_user|tambah_user|edit_user|hapus_user');

    Route::resource('kategori-produk', KategoriProdukController::class)
        ->middleware('permission:lihat_kategori_produk|tambah_kategori_produk|edit_kategori_produk|hapus_kategori_produk');

    Route::resource('produk', ProdukController::class)
        ->middleware('permission:lihat_produk|tambah_produk|edit_produk|hapus_produk');

    Route::resource('bahan-baku', BahanBakuController::class)
        ->middleware('permission:lihat_stok|tambah_stok|edit_stok|hapus_stok');

    Route::resource('produksi', ProduksiController::class)
        ->middleware('permission:lihat_produksi|tambah_produksi|edit_produksi|hapus_produksi');

    Route::resource('pegawai', PegawaiController::class)
        ->middleware('permission:lihat_pegawai|tambah_pegawai|edit_pegawai|hapus_pegawai');

    Route::resource('member', MemberController::class)
        ->middleware('permission:lihat_member|tambah_member|edit_member|hapus_member');

    // Promo Produk
    Route::middleware('permission:lihat_promo')->group(function () {
        Route::get('/promo', [PromoController::class, 'indeks'])->name('promo.index');
        Route::get('/promo/{id}/ubah', [PromoController::class, 'ubah'])->name('promo.edit');
    });
    Route::middleware('permission:tambah_promo')->group(function () {
        Route::get('/promo/tambah', [PromoController::class, 'tambah'])->name('promo.create');
        Route::post('/promo', [PromoController::class, 'simpan'])->name('promo.store');
    });
    Route::middleware('permission:edit_promo')->group(function () {
        Route::put('/promo/{id}', [PromoController::class, 'perbarui'])->name('promo.update');
    });
    Route::middleware('permission:hapus_promo')->group(function () {
        Route::delete('/promo/{id}', [PromoController::class, 'hapus'])->name('promo.destroy');
    });

    // calendar
    Route::get('/calendar', function () {
        return view('pages.calender', ['title' => 'Calendar']);
    })->name('calendar');

    // profile (Breeze, fungsional: edit/update/destroy)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // form pages
    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', ['title' => 'Form Elements']);
    })->name('form-elements');

    // tables pages
    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('basic-tables');

    Route::get('/blank', function () {
        return view('pages.blank', ['title' => 'Blank']);
    })->name('blank');

    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => 'Error 404']);
    })->name('error-404');

    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('bar-chart');

    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', ['title' => 'Badges']);
    })->name('badges');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('buttons');

    Route::get('/image', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');
});

require __DIR__.'/auth.php';