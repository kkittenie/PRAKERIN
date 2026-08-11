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

class PesananPickupController extends Controller
{
    /**
     * Daftar pesanan Pick Up yang masuk dari Member App. Kasir lihat semua
     * pesanan yang masih berjalan (diproses/siap diambil) plus riwayat
     * ringkas yang sudah selesai/dibatalkan.
     */
    public function indeks(Request $request)
    {
        $status = $request->query('status', 'aktif');

        $query = Transaksi::with(['detailTransaksi', 'member'])
            ->where('asal_pesanan', Transaksi::ASAL_MEMBER_APP);

        if ($status === 'aktif') {
            $query->whereIn('status_pesanan', [Transaksi::STATUS_DIPROSES, Transaksi::STATUS_SIAP_DIAMBIL]);
        } elseif ($status !== 'semua') {
            $query->where('status_pesanan', $status);
        }

        $pesanan = $query->orderBy(
            // Yang "Diproses" ditampilkan duluan (perlu aksi kasir), baru "Siap Diambil".
            DB::raw("FIELD(status_pesanan, '" . Transaksi::STATUS_DIPROSES . "', '" . Transaksi::STATUS_SIAP_DIAMBIL . "')")
        )->latest()->get();

        $jumlahDiproses = Transaksi::where('asal_pesanan', Transaksi::ASAL_MEMBER_APP)
            ->where('status_pesanan', Transaksi::STATUS_DIPROSES)->count();
        $jumlahSiap = Transaksi::where('asal_pesanan', Transaksi::ASAL_MEMBER_APP)
            ->where('status_pesanan', Transaksi::STATUS_SIAP_DIAMBIL)->count();

        return view('pages.pesanan-pickup.index', [
            'pesanan' => $pesanan,
            'statusFilter' => $status,
            'jumlahDiproses' => $jumlahDiproses,
            'jumlahSiap' => $jumlahSiap,
        ]);
    }

    /**
     * Ubah status pesanan: Diproses -> Ready for Pickup. (Dari "Ready for
     * Pickup" ke "Sudah Diambil" wajib lewat verifikasi kode di
     * verifikasiKode(), bukan lewat sini, supaya kode pickup benar-benar
     * dicek ke pembeli.)
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

            $transaksi->update([
                'status_pesanan' => Transaksi::STATUS_SIAP_DIAMBIL,
                'siap_pada' => now(),
            ]);

            DB::commit();

            activity()->causedBy(auth()->user())->performedOn($transaksi)
                ->log('menandai pesanan pick up siap diambil');

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

            return back()->with('success', "Pesanan {$transaksi->kode_transaksi} dibatalkan, stok & voucher dikembalikan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batalkan pesanan pickup: ' . $e->getMessage());
            return back()->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi kode pickup yang ditunjukkan member ke kasir. Ini titik
     * serah-terima: pesanan ditandai Sudah Diambil, pembayaran dilunaskan,
     * dan baru di sinilah member mendapat poin & stamp — sama seperti
     * transaksi kasir biasa (TransaksiKasirController::bayar), supaya
     * member tidak mendapat poin untuk pesanan yang batal/tidak diambil.
     */
    public function verifikasiKode(Request $request)
    {
        $validated = $request->validate([
            'kode_pickup' => 'required|string|max:10',
            'metode_pembayaran' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::with(['member', 'detailTransaksi'])
                ->where('kode_pickup', strtoupper(trim($validated['kode_pickup'])))
                ->where('asal_pesanan', Transaksi::ASAL_MEMBER_APP)
                ->lockForUpdate()
                ->first();

            if (! $transaksi) {
                throw new \Exception('Kode pickup tidak ditemukan.');
            }

            if ($transaksi->status_pesanan === Transaksi::STATUS_SUDAH_DIAMBIL) {
                throw new \Exception('Pesanan ini sudah pernah diambil sebelumnya.');
            }

            if ($transaksi->status_pesanan === Transaksi::STATUS_DIBATALKAN) {
                throw new \Exception('Pesanan ini sudah dibatalkan.');
            }

            if ($transaksi->status_pesanan !== Transaksi::STATUS_SIAP_DIAMBIL) {
                throw new \Exception('Pesanan ini belum siap diambil (masih Diproses).');
            }

            $transaksi->update([
                'status_pesanan' => Transaksi::STATUS_SUDAH_DIAMBIL,
                'status_pembayaran' => 'Lunas',
                'metode_pembayaran' => $validated['metode_pembayaran'] ?? 'Tunai',
                'jumlah_diterima' => $transaksi->total_bayar,
                'kembalian' => 0,
                'diambil_pada' => now(),
            ]);

            $this->berikanPoinDanStamp($transaksi);

            DB::commit();

            activity()->causedBy(auth()->user())->performedOn($transaksi)
                ->withProperties(['kode_pickup' => $transaksi->kode_pickup])
                ->log('memverifikasi & menyerahkan pesanan pick up');

            return response()->json([
                'status' => 'success',
                'pesan' => "Pesanan {$transaksi->kode_transaksi} berhasil diserahkan ke {$transaksi->nama_pembeli}.",
                'data' => $transaksi,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'pesan' => $e->getMessage(),
            ], 422);
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