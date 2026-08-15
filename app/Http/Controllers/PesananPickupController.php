<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberStamp;
use App\Models\PengaturanMember;
use App\Models\StampProgram;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PesananPickupController extends Controller
{
    public function indeks(Request $request)
    {
        $statusPesanan = $request->query('status_pesanan', 'semua');
        $statusPembayaran = $request->query('status_pembayaran', 'semua');
        $cari = $request->query('cari');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');

        $query = Transaksi::with(['detailTransaksi', 'member'])
            ->where('asal_pesanan', Transaksi::ASAL_MEMBER_APP);

        if ($statusPesanan !== 'semua') {
            $query->where('status_pesanan', $statusPesanan);
        }

        if ($statusPembayaran === 'lunas') {
            $query->where('status_pembayaran', 'Lunas');
        } elseif ($statusPembayaran === 'menunggu_konfirmasi') {
            $query->whereNotNull('bukti_dikonfirmasi_pada')->where('status_pembayaran', '!=', 'Lunas');
        } elseif ($statusPembayaran === 'menunggu_pembayaran') {
            $query->whereNull('bukti_dikonfirmasi_pada')->where('status_pembayaran', '!=', 'Lunas');
        }

        if ($cari) {
            $query->where(function ($q) use ($cari) {
                $q->where('kode_transaksi', 'like', "%{$cari}%")
                  ->orWhere('nama_pembeli', 'like', "%{$cari}%");
            });
        }

        if ($tanggalMulai) {
            $query->whereDate('created_at', '>=', $tanggalMulai);
        }
        if ($tanggalSelesai) {
            if ($tanggalMulai && $tanggalSelesai < $tanggalMulai) {
                $tanggalSelesai = $tanggalMulai;
            }
            $query->whereDate('created_at', '<=', $tanggalSelesai);
        }

        $pesanan = $query->latest()->paginate(10)->withQueryString();

        // Dipanggil via fetch() dari dengarkanPembaruanRealtime() di
        // index.blade.php tiap ada siaran real-time — cukup kirim balik
        // HTML tabelnya saja (bukan seluruh halaman) supaya bisa disuntik
        // langsung ke DOM tanpa reload.
        if ($request->ajax()) {
            return view('pages.pesanan-pickup._tabel', [
                'pesanan' => $pesanan,
            ]);
        }

        return view('pages.pesanan-pickup.index', [
            'pesanan' => $pesanan,
            'statusPesananFilter' => $statusPesanan,
            'statusPembayaranFilter' => $statusPembayaran,
        ]);
    }

    /**
     * JSON API untuk modal detail (pola sama seperti LaporanController::detailTransaksi).
     */
    public function detail(Transaksi $transaksi)
    {
        abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

        return response()->json([
            'status' => 'success',
            'data' => $transaksi->load(['detailTransaksi.produk', 'member']),
        ]);
    }

    /**
     * Kasir mengonfirmasi bukti transfer/QRIS yang sudah dikunci member
     * sebagai valid. Menandai pesanan Lunas.
     */
    public function konfirmasiPembayaran(Transaksi $transaksi)
    {
        abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

        if ($transaksi->status_pembayaran === 'Lunas') {
            return back()->with('error', 'Pesanan ini sudah lunas.');
        }
        if (! $transaksi->bukti_dikonfirmasi_pada) {
            return back()->with('error', 'Member belum mengonfirmasi bukti pembayaran.');
        }

        $transaksi->update([
            'status_pembayaran' => 'Lunas',
            'jumlah_diterima' => $transaksi->total_bayar,
            'kembalian' => 0,
        ]);

        activity()->causedBy(auth()->user())->performedOn($transaksi)
            ->log('mengonfirmasi bukti pembayaran pesanan pick up');

        event(new \App\Events\PesananPickupUpdated($transaksi, 'update'));

        return back()->with('success', "Pembayaran pesanan {$transaksi->kode_transaksi} dikonfirmasi.");
    }

    /**
     * Kasir menolak bukti transfer/QRIS yang tidak valid. Bukti dihapus &
     * member diminta upload ulang lewat member app (form upload otomatis
     * muncul lagi karena bukti_pembayaran jadi null).
     */
    public function tolakPembayaran(Request $request, Transaksi $transaksi)
    {
        abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

        $validated = $request->validate([
            'alasan' => 'nullable|string|max:255',
        ]);

        if ($transaksi->status_pembayaran === 'Lunas') {
            return back()->with('error', 'Pesanan ini sudah lunas, tidak bisa ditolak.');
        }

        if ($transaksi->bukti_pembayaran) {
            Storage::disk('public')->delete($transaksi->bukti_pembayaran);
        }

        $transaksi->update([
            'bukti_pembayaran' => null,
            'bukti_dikonfirmasi_pada' => null,
            'catatan_penolakan' => $validated['alasan'] ?: 'Bukti pembayaran tidak valid, silakan upload ulang.',
        ]);

        activity()->causedBy(auth()->user())->performedOn($transaksi)
            ->log('menolak bukti pembayaran pesanan pick up');

        event(new \App\Events\PesananPickupUpdated($transaksi, 'update'));

        return back()->with('success', "Bukti pembayaran {$transaksi->kode_transaksi} ditolak, member diminta upload ulang.");
    }

    /**
     * Menandai pesanan mulai dikerjakan setelah pembayaran lunas. Titik ini
     * juga jadi patokan kapan Tiket Produksi relevan dicetak (di modal).
     */
    public function prosesPesanan(Transaksi $transaksi)
    {
        abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

        if ($transaksi->status_pembayaran !== 'Lunas') {
            return back()->with('error', 'Pembayaran belum dikonfirmasi.');
        }
        if ($transaksi->diproses_pada) {
            return back()->with('error', 'Pesanan ini sudah diproses.');
        }

        $transaksi->update(['diproses_pada' => now()]);

        activity()->causedBy(auth()->user())->performedOn($transaksi)
            ->log('memproses pesanan pick up');

        event(new \App\Events\PesananPickupUpdated($transaksi, 'update'));

        return back()->with('success', "Pesanan {$transaksi->kode_transaksi} mulai diproses.");
    }

    /**
     * Ubah status pesanan: Diproses -> Ready for Pickup. Wajib sudah lunas
     * & sudah lewat tahap "Proses Pesanan".
     *
     * Stok baru dikurangi di sini, bukan saat member checkout di app —
     * supaya stok toko mencerminkan barang yang benar-benar sudah disiapkan
     * kasir untuk pesanan ini, bukan sekadar dipesan lewat app.
     */
    public function tandaiSiap(Transaksi $transaksi)
    {
        abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

        if ($transaksi->status_pesanan !== Transaksi::STATUS_DIPROSES) {
            return back()->with('error', 'Pesanan ini bukan berstatus Diproses.');
        }
        if ($transaksi->status_pembayaran !== 'Lunas' || ! $transaksi->diproses_pada) {
            return back()->with('error', 'Selesaikan konfirmasi pembayaran & proses pesanan dulu.');
        }

        DB::beginTransaction();
        try {
            $transaksi->load('detailTransaksi');

            foreach ($transaksi->detailTransaksi as $detail) {
                if ($detail->butuh_produksi || ! $detail->produk_id) {
                    continue;
                }

                $produk = \App\Models\Produk::lockForUpdate()->find($detail->produk_id);
                if (! $produk) {
                    continue;
                }

                if ($produk->stok < $detail->jumlah) {
                    throw new \Exception("Stok untuk produk '{$produk->nama_produk}' tidak mencukupi untuk disiapkan.");
                }

                $produk->decrement('stok', $detail->jumlah);
            }

            // Kode pickup baru digenerate di sini (bukan waktu member
            // checkout) supaya kode yang ditunjukkan ke kasir cuma berlaku
            // begitu pesanan beneran siap diambil.
            $transaksi->update([
                'status_pesanan' => Transaksi::STATUS_SIAP_DIAMBIL,
                'siap_pada' => now(),
                'kode_pickup' => $transaksi->kode_pickup ?: Transaksi::buatKodePickupUnik(),
            ]);

            DB::commit();

            activity()->causedBy(auth()->user())->performedOn($transaksi)
                ->log('menandai pesanan pick up siap diambil');

            event(new \App\Events\PesananPickupUpdated($transaksi, 'update'));

            return back()->with('success', "Pesanan {$transaksi->kode_transaksi} ditandai siap diambil.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error tandai siap pesanan pickup: ' . $e->getMessage());
            return back()->with('error', 'Gagal menandai siap: ' . $e->getMessage());
        }
    }

    public function batalkan(Transaksi $transaksi)
    {
        abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

        if (in_array($transaksi->status_pesanan, [Transaksi::STATUS_SUDAH_DIAMBIL, Transaksi::STATUS_DIBATALKAN], true)) {
            return back()->with('error', 'Pesanan ini sudah tidak bisa dibatalkan.');
        }

        DB::beginTransaction();
        try {
            // Stok cuma dikurangi saat pesanan ditandai siap (lihat
            // tandaiSiap()). Jadi kalau dibatalkan saat masih "Diproses",
            // stok belum pernah dikurangi & tidak perlu dikembalikan. Stok
            // baru dikembalikan kalau dibatalkan dari status "Siap Diambil".
            if ($transaksi->status_pesanan === Transaksi::STATUS_SIAP_DIAMBIL) {
                foreach ($transaksi->detailTransaksi as $detail) {
                    if (! $detail->butuh_produksi && $detail->produk_id) {
                        $detail->produk?->increment('stok', $detail->jumlah);
                    }
                }
            }

            if ($transaksi->voucher_id && $transaksi->voucher) {
                $transaksi->voucher->update(['status' => 'menunggu_diambil', 'diambil_pada' => null, 'transaksi_id' => null]);
            }

            $transaksi->update(['status_pesanan' => Transaksi::STATUS_DIBATALKAN]);

            DB::commit();

            activity()->causedBy(auth()->user())->performedOn($transaksi)
                ->log('membatalkan pesanan pick up dari member app');

            event(new \App\Events\PesananPickupUpdated($transaksi, 'update'));

            return back()->with('success', "Pesanan {$transaksi->kode_transaksi} dibatalkan, stok & voucher dikembalikan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batalkan pesanan pickup: ' . $e->getMessage());
            return back()->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Kasir menandai pesanan sudah diserahkan ke pembeli. Wajib memasukkan
     * kode pickup yang ditunjukkan member dari aplikasinya dulu, supaya
     * pesanan nggak ketuker/diserahkan ke orang yang salah.
     *
     * Dipanggil via fetch() dari modal "Verifikasi Kode Pickup" di
     * index.blade.php (bukan submit form biasa), supaya kalau kodenya
     * salah, pesannya bisa ditampilkan di dalam modal itu sendiri tanpa
     * modal ikut ketutup/halaman reload. Makanya semua respons di sini
     * berupa JSON, bukan redirect back().
     */
    public function tandaiSudahDiambil(Request $request, Transaksi $transaksi)
    {
        abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

        if ($transaksi->status_pesanan !== Transaksi::STATUS_SIAP_DIAMBIL) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan ini belum siap diambil.',
            ], 422);
        }

        $validated = $request->validate([
            'kode_pickup' => 'required|string|max:6',
        ]);

        $kodeInput = strtoupper(trim($validated['kode_pickup']));
        if (! $transaksi->kode_pickup || $kodeInput !== $transaksi->kode_pickup) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode pickup tidak sesuai. Minta pembeli menunjukkan kode dari aplikasi member.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $transaksi->update([
                'status_pesanan' => Transaksi::STATUS_SUDAH_DIAMBIL,
                'diambil_pada' => now(),
            ]);

            $this->berikanPoinDanStamp($transaksi);

            DB::commit();

            activity()->causedBy(auth()->user())->performedOn($transaksi)
                ->log('menyerahkan pesanan pick up');

            event(new \App\Events\PesananPickupUpdated($transaksi, 'update'));

            return response()->json([
                'status' => 'success',
                'message' => "Pesanan {$transaksi->kode_transaksi} berhasil diserahkan.",
                'data' => $transaksi->fresh()->load(['detailTransaksi.produk', 'member']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error tandai sudah diambil: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menandai sudah diambil: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sama persis dengan aturan poin & stamp di TransaksiKasirController::bayar(),
     * supaya member yang order lewat app diperlakukan sama dengan yang
     * dilayani langsung oleh kasir.
     */
    private function berikanPoinDanStamp(Transaksi $transaksi): void
    {
        if (! $transaksi->member_id) {
            return;
        }

        $pengaturan = PengaturanMember::where('status_aktif', true)->first();
        if ($pengaturan) {
            $nominalUntukPoin = $transaksi->subtotal + $transaksi->pajak;
            $kelipatan = floor($nominalUntukPoin / $pengaturan->minimal_belanja);
            $poin = $kelipatan * $pengaturan->poin;

            $member = Member::find($transaksi->member_id);
            if ($member) {
                $poinSebelum = $member->poin;
                if ($poin > 0) {
                    $member->increment('poin', $poin);
                }
                $transaksi->update([
                    'poin_sebelum' => $poinSebelum,
                    'poin_didapat' => (int) $poin,
                ]);
            }
        }

        $stampProgram = StampProgram::aktifSekarang();
        if ($stampProgram) {
            $nominalUntukStamp = $transaksi->subtotal + $transaksi->pajak;
            $stampDidapat = (int) floor($nominalUntukStamp / $stampProgram->minimal_pembelian);

            if ($stampDidapat > 0) {
                $memberStamp = MemberStamp::firstOrCreate(
                    ['member_id' => $transaksi->member_id, 'stamp_program_id' => $stampProgram->id],
                    ['jumlah_stamp' => 0]
                );

                $stampSebelum = $memberStamp->jumlah_stamp;
                $stampBaru = min($stampSebelum + $stampDidapat, $stampProgram->jumlah_stamp_dibutuhkan);

                $memberStamp->update(['jumlah_stamp' => $stampBaru]);

                $transaksi->update([
                    'stamp_program_id' => $stampProgram->id,
                    'stamp_sebelum' => $stampSebelum,
                    'stamp_didapat' => $stampBaru - $stampSebelum,
                ]);
            }
        }
    }
}