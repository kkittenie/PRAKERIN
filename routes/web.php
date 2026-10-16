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
use App\Http\Controllers\BundlingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TransaksiBahanBakuController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PengaturanMemberController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\StampProgramController;
use App\Http\Controllers\RewardVoucherController;
use App\Http\Controllers\RiwayatRedeemController;
use App\Http\Controllers\MemberLevelController;
use App\Http\Controllers\Mobile\StampController as MemberStampController;
use App\Http\Controllers\VoucherKasirController;
use App\Http\Controllers\StampRedemptionKasirController;
use App\Http\Controllers\PesananPickupController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\WelcomeSlideController;
use App\Http\Controllers\QrisController;
use App\Http\Controllers\PengaturanPajakController;
use App\Http\Controllers\KontenStatisController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PromoProdukEventController;
use App\Http\Controllers\Mobile\AuthController as MobileAuthController;

    require __DIR__ . '/mobile.php';

    // Root nampilin halaman welcome mobile langsung (tanpa redirect)
    Route::get('/', [MobileAuthController::class, 'welcome'])->name('member.welcome.root');

    Route::middleware(['auth'])->group(function () {

    // Notifikasi Real-time
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/unread-json', [NotificationController::class, 'unreadJson'])->name('notifications.unread-json');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifikasi/{id}/baca', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

    // Dashboard Utama (Beranda)
    Route::get('/admin', [DashboardController::class, 'index'])
        ->middleware('permission:lihat_dashboard')
        ->name('dashboard');

    // Transaksi Kasir (POS)
    Route::middleware('permission:lihat_transaksi')->group(function () {
        Route::get('/transaksi', [TransaksiKasirController::class, 'indeks'])->name('transaksi.indeks');
        Route::get('/transaksi/daftar-ditahan', [TransaksiKasirController::class, 'ambilDitahan'])->name('transaksi.daftar-ditahan');
        Route::get('/transaksi/cetak/{kode_transaksi}', [TransaksiKasirController::class, 'cetak'])->name('transaksi.cetak');
        Route::get('/transaksi/voucher/member/{member}', [VoucherKasirController::class, 'daftarAktif'])->name('transaksi.voucher.member');
        Route::post('/transaksi/voucher/validasi', [VoucherKasirController::class, 'validasiKode'])->name('transaksi.voucher.validasi');
        Route::get('/transaksi/reward-stamp/member/{member}', [StampRedemptionKasirController::class, 'daftarAktif'])->name('transaksi.reward-stamp.member');
        Route::post('/transaksi/reward-stamp/validasi', [StampRedemptionKasirController::class, 'validasiKode'])->name('transaksi.reward-stamp.validasi');
        Route::get('/transaksi/promo-status', [TransaksiKasirController::class, 'promoStatus'])->name('transaksi.promo-status');
    });
    Route::middleware('permission:tambah_transaksi')->group(function () {
        Route::post('/transaksi/tahan', [TransaksiKasirController::class, 'simpanDitahan'])->name('transaksi.tahan');
        Route::post('/transaksi/pulihkan/{id}', [TransaksiKasirController::class, 'pulihkanDitahan'])->name('transaksi.pulihkan');
        Route::delete('/transaksi/hapus-ditahan/{id}', [TransaksiKasirController::class, 'hapusDitahan'])->name('transaksi.hapus-ditahan');
        Route::post('/transaksi/bayar', [TransaksiKasirController::class, 'bayar'])->name('transaksi.bayar');
        Route::post('/transaksi/lunaskan/{id}', [TransaksiKasirController::class, 'lunaskanDitahan'])->name('transaksi.lunaskan');
    });

    // Pesanan (pesanan masuk dari Member App)
    Route::middleware('permission:lihat_pesanan_pickup')->group(function () {
        Route::get('/pesanan-pickup', [PesananPickupController::class, 'indeks'])->name('pesanan-pickup.indeks');
        Route::get('/pesanan-pickup/{transaksi}/detail', [PesananPickupController::class, 'detail'])->name('pesanan-pickup.detail');
    });
    Route::middleware('permission:edit_pesanan_pickup')->group(function () {
        Route::patch('/pesanan-pickup/{transaksi}/konfirmasi-pembayaran', [PesananPickupController::class, 'konfirmasiPembayaran'])->name('pesanan-pickup.konfirmasi-pembayaran');
        Route::patch('/pesanan-pickup/{transaksi}/tolak-pembayaran', [PesananPickupController::class, 'tolakPembayaran'])->name('pesanan-pickup.tolak-pembayaran');
        Route::patch('/pesanan-pickup/{transaksi}/proses', [PesananPickupController::class, 'prosesPesanan'])->name('pesanan-pickup.proses');
        Route::patch('/pesanan-pickup/{transaksi}/siap', [PesananPickupController::class, 'tandaiSiap'])->name('pesanan-pickup.siap');
        Route::patch('/pesanan-pickup/{transaksi}/selesai', [PesananPickupController::class, 'tandaiSudahDiambil'])->name('pesanan-pickup.selesai');
        Route::patch('/pesanan-pickup/{transaksi}/batal', [PesananPickupController::class, 'batalkan'])->name('pesanan-pickup.batal');
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

    Route::patch('/banner/{banner}/toggle-status', [BannerController::class, 'toggleStatus'])
        ->name('banner.toggle-status')
        ->middleware('permission:edit_banner');
    
    Route::resource('banner', BannerController::class)
        ->middleware('permission:lihat_banner|tambah_banner|edit_banner|hapus_banner');    

    // Slide halaman welcome/login member (gambar dinamis)
    Route::patch('/welcome-slide/{welcome_slide}/toggle-status', [WelcomeSlideController::class, 'toggleStatus'])
        ->name('welcome-slide.toggle-status')
        ->middleware('permission:edit_welcome_slide');

    Route::resource('welcome-slide', WelcomeSlideController::class)
        ->except(['show'])
        ->parameters(['welcome-slide' => 'welcome_slide'])
        ->middleware('permission:lihat_welcome_slide|tambah_welcome_slide|edit_welcome_slide|hapus_welcome_slide');

    // QRIS pembayaran pesanan online (ganti gambar dinamis)
    Route::middleware('permission:lihat_qris')->group(function () {
        Route::get('/qris', [QrisController::class, 'index'])->name('qris.index');
    });
    Route::middleware('permission:tambah_qris')->group(function () {
        Route::get('/qris/create', [QrisController::class, 'create'])->name('qris.create');
        Route::post('/qris', [QrisController::class, 'store'])->name('qris.store');
    });
    Route::middleware('permission:edit_qris')->group(function () {
        Route::get('/qris/{qris}/edit', [QrisController::class, 'edit'])->name('qris.edit');
        Route::put('/qris/{qris}', [QrisController::class, 'update'])->name('qris.update');
        Route::patch('/qris/{qris}/toggle-status', [QrisController::class, 'toggleStatus'])->name('qris.toggle-status');
    });
    Route::middleware('permission:hapus_qris')->group(function () {
        Route::delete('/qris/{qris}', [QrisController::class, 'destroy'])->name('qris.destroy');
    });

    // Pengaturan Pajak
    Route::middleware('permission:lihat_pajak')->group(function () {
        Route::get('/pengaturan-pajak', [PengaturanPajakController::class, 'index'])->name('pengaturan-pajak.index');
    });
    Route::middleware('permission:tambah_pajak')->group(function () {
        Route::get('/pengaturan-pajak/tambah', [PengaturanPajakController::class, 'create'])->name('pengaturan-pajak.create');
        Route::post('/pengaturan-pajak', [PengaturanPajakController::class, 'store'])->name('pengaturan-pajak.store');
    });
    Route::middleware('permission:edit_pajak')->group(function () {
        Route::get('/pengaturan-pajak/{id}/ubah', [PengaturanPajakController::class, 'edit'])->name('pengaturan-pajak.edit');
        Route::put('/pengaturan-pajak/{id}', [PengaturanPajakController::class, 'update'])->name('pengaturan-pajak.update');
        Route::patch('/pengaturan-pajak/{id}/toggle-status', [PengaturanPajakController::class, 'toggleStatus'])->name('pengaturan-pajak.toggle-status');
    });
    Route::middleware('permission:hapus_pajak')->group(function () {
        Route::delete('/pengaturan-pajak/{id}', [PengaturanPajakController::class, 'destroy'])->name('pengaturan-pajak.destroy');
    });

    // Konten Statis
    Route::get('/konten-statis', [KontenStatisController::class, 'index'])->name('konten-statis.index');
    Route::get('/konten-statis/{slug}/edit', [KontenStatisController::class, 'edit'])->name('konten-statis.edit');
    Route::put('/konten-statis/{slug}', [KontenStatisController::class, 'update'])->name('konten-statis.update');

    Route::patch('/produk/{produk}/toggle-status', [ProdukController::class, 'toggleStatus'])->name('produk.toggle-status');

    Route::resource('produk', ProdukController::class)
        ->middleware('permission:lihat_produk|tambah_produk|edit_produk|hapus_produk');

    Route::resource('bahan-baku', BahanBakuController::class)
        ->middleware('permission:lihat_stok|tambah_stok|edit_stok|hapus_stok');

    // Laporan Produksi & Live Status Realtime
    Route::get('/produksi/live-status', [ProduksiController::class, 'liveStatus'])
        ->name('produksi.live-status');

    Route::middleware('permission:lihat_laporan')->group(function () {

        Route::get('/produksi/pdf', [ProduksiController::class, 'pdf'])
            ->name('produksi.pdf');

        Route::get('/produksi/excel', [ProduksiController::class, 'excel'])
            ->name('produksi.excel');
    });

    Route::resource('produksi', ProduksiController::class)
        ->middleware('permission:lihat_produksi|tambah_produksi|edit_produksi|hapus_produksi');

    Route::patch('/bundling/{bundling}/toggle-status', [BundlingController::class, 'toggleStatus'])
        ->name('bundling.toggle-status')
        ->middleware('permission:edit_bundling');

    Route::resource('bundling', BundlingController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->middleware('permission:lihat_bundling|tambah_bundling|edit_bundling|hapus_bundling');

    Route::post('produksi/{produksi}/kurangi-stok', [ProduksiController::class, 'kurangiStok'])
        ->name('produksi.kurangi-stok')
        ->middleware('permission:lihat_produksi|tambah_produksi|edit_produksi|hapus_produksi');

    Route::get('produksi/{produksi}/label', [ProduksiController::class, 'label'])
        ->name('produksi.label')
        ->middleware('permission:lihat_produksi|tambah_produksi|edit_produksi|hapus_produksi');

    Route::resource('pegawai', PegawaiController::class)
        ->middleware('permission:lihat_pegawai|tambah_pegawai|edit_pegawai|hapus_pegawai');

    Route::resource('member', MemberController::class)
        ->middleware('permission:lihat_member|tambah_member|edit_member|hapus_member');

    Route::post('/member/{member}/reset-password', [MemberController::class, 'resetPassword'])
        ->name('member.reset-password')
        ->middleware('permission:edit_member');

    Route::resource('transaksi-bahan-baku', TransaksiBahanBakuController::class)
        ->only(['index', 'create', 'store', 'show',])
        ->middleware('permission:lihat_stok|tambah_stok');

    Route::resource('satuan', SatuanController::class)
        ->middleware('permission:lihat_satuan|tambah_satuan|edit_satuan|hapus_satuan');

    Route::post('/satuan/ajax', [SatuanController::class, 'storeAjax'])
        ->name('satuan.storeAjax')
        ->middleware('permission:tambah_satuan');




    // Promo Produk
    Route::middleware('permission:lihat_promo')->group(function () {
        Route::get('/promo', [PromoController::class, 'indeks'])->name('promo.index');
        Route::get('/promo/{id}', [PromoController::class, 'lihat'])->whereNumber('id')->name('promo.show');
        Route::get('/promo/{id}/ubah', [PromoController::class, 'ubah'])->whereNumber('id')->name('promo.edit');
    });
    Route::middleware('permission:tambah_promo')->group(function () {
        Route::get('/promo/tambah', [PromoController::class, 'tambah'])->name('promo.create');
        Route::post('/promo', [PromoController::class, 'simpan'])->name('promo.store');
    });
    Route::middleware('permission:edit_promo')->group(function () {
        Route::put('/promo/{id}', [PromoController::class, 'perbarui'])->name('promo.update');
        Route::patch('/promo/{id}/toggle-status', [PromoController::class, 'toggleStatus'])->name('promo.toggle-status');
    });
    Route::middleware('permission:hapus_promo')->group(function () {
        Route::delete('/promo/{id}', [PromoController::class, 'hapus'])->name('promo.destroy');
    });

    // Promo Produk Event
    Route::middleware('permission:lihat_promo_event')->group(function () {
        Route::get('/promo-event', [PromoProdukEventController::class, 'indeks'])->name('promo-event.index');
        Route::get('/promo-event/{id}', [PromoProdukEventController::class, 'lihat'])->whereNumber('id')->name('promo-event.show');
        Route::get('/promo-event/{id}/ubah', [PromoProdukEventController::class, 'ubah'])->whereNumber('id')->name('promo-event.edit');
    });
    Route::middleware('permission:tambah_promo_event')->group(function () {
        Route::get('/promo-event/tambah', [PromoProdukEventController::class, 'tambah'])->name('promo-event.create');
        Route::post('/promo-event', [PromoProdukEventController::class, 'simpan'])->name('promo-event.store');
    });
    Route::middleware('permission:edit_promo_event')->group(function () {
        Route::put('/promo-event/{id}', [PromoProdukEventController::class, 'perbarui'])->name('promo-event.update');
        Route::patch('/promo-event/{id}/toggle-status', [PromoProdukEventController::class, 'toggleStatus'])->name('promo-event.toggle-status');
    });
    Route::middleware('permission:hapus_promo_event')->group(function () {
        Route::delete('/promo-event/{id}', [PromoProdukEventController::class, 'hapus'])->name('promo-event.destroy');
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

    // pengaturan member
    Route::middleware('permission:lihat_member')->group(function () {
        Route::get('/pengaturan-member', [PengaturanMemberController::class, 'index'])
            ->name('pengaturan-member.index');
    });

    Route::middleware('permission:tambah_member')->group(function () {
        Route::get('/pengaturan-member/tambah', [PengaturanMemberController::class, 'create'])
            ->name('pengaturan-member.create');

        Route::post('/pengaturan-member', [PengaturanMemberController::class, 'store'])
            ->name('pengaturan-member.store');
    });

    Route::middleware('permission:edit_member')->group(function () {
        Route::get('/pengaturan-member/{id}/ubah', [PengaturanMemberController::class, 'edit'])
            ->name('pengaturan-member.edit');

        Route::put('/pengaturan-member/{id}', [PengaturanMemberController::class, 'update'])
            ->name('pengaturan-member.update');

        Route::patch('/pengaturan-member/{id}/toggle-status', [PengaturanMemberController::class, 'toggleStatus'])
            ->name('pengaturan-member.toggle-status');
    });

    Route::middleware('permission:hapus_member')->group(function () {
        Route::delete('/pengaturan-member/{id}', [PengaturanMemberController::class, 'destroy'])
            ->name('pengaturan-member.destroy');
    });

    // Reward Stamp
    Route::patch('/stamp-program/{stampProgram}/toggle-status', [StampProgramController::class, 'toggleStatus'])
        ->name('stamp-program.toggle-status')
        ->middleware('permission:edit_reward');

    Route::resource('stamp-program', StampProgramController::class)
        ->middleware('permission:lihat_reward|tambah_reward|edit_reward|hapus_reward');

    // Reward Voucher
    Route::patch('/reward-voucher/{rewardVoucher}/toggle-status', [RewardVoucherController::class, 'toggleStatus'])
        ->name('reward-voucher.toggle-status')
        ->middleware('permission:edit_reward');
    Route::post('/reward-voucher/{rewardVoucher}/tambah-member', [RewardVoucherController::class, 'tambahMember'])
        ->name('reward-voucher.tambah-member')
        ->middleware('permission:edit_reward');
    Route::delete('/reward-voucher/{rewardVoucher}/cabut-member/{member}', [RewardVoucherController::class, 'cabutMember'])
        ->name('reward-voucher.cabut-member')
        ->middleware('permission:edit_reward');

    Route::resource('reward-voucher', RewardVoucherController::class)
        ->middleware('permission:lihat_reward|tambah_reward|edit_reward|hapus_reward');

    // Riwayat Redeem (histori penukaran poin milik seluruh member)
    Route::middleware('permission:lihat_reward')->group(function () {
        Route::get('/riwayat-redeem', [RiwayatRedeemController::class, 'index'])
            ->name('riwayat-redeem.index');
    });

    Route::patch('/riwayat-redeem/{riwayatRedeem}/status', [RiwayatRedeemController::class, 'updateStatus'])
        ->name('riwayat-redeem.update-status')
        ->middleware('permission:edit_reward');

    // Aturan Naik Level
    Route::patch('/member-level/{memberLevel}/toggle-status', [MemberLevelController::class, 'toggleStatus'])
        ->name('member-level.toggle-status')
        ->middleware('permission:edit_member');

    Route::resource('member-level', MemberLevelController::class)
        ->except(['show'])
        ->middleware('permission:lihat_member|tambah_member|edit_member|hapus_member');

    // Activity Log
    Route::middleware('permission:lihat_activity_log')->group(function () {
        Route::get('/activity-Log', [ActivityLogController::class, 'index'])->name('activity-log');
    });
});

require __DIR__ . '/auth.php';

Route::fallback(function () {
    abort(404);
});