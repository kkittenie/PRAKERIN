<?php

namespace App\Support;

use App\Events\PesananPickupUpdated;
use App\Models\DetailProduk;
use App\Models\StampRedemption;
use App\Models\Transaksi;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Logika pembatalan pesanan pickup dari Member App, dipakai bersama oleh:
 * - admin/kasir (PesananPickupController::batalkan), dan
 * - sistem, saat member tidak upload bukti pembayaran sampai batas waktunya
 *   (command terjadwal + pengecekan saat member membuka halaman pesanan).
 *
 * Hasilnya sama persis: stok dikembalikan, jatah voucher dikembalikan,
 * hadiah stamp kembali ke "menunggu diambil", status jadi Dibatalkan.
 */
class PembatalanPesananPickup
{
    /**
     * Batalkan pesanan (validasi status sudah dilakukan pemanggil).
     * Semua perubahan dalam satu transaksi DB; kalau gagal di-rollback lalu
     * exception dilempar lagi.
     *
     * @return int jumlah hadiah stamp yang dikembalikan ke status menunggu diambil
     */
    public static function batalkan(Transaksi $transaksi, string $alasan, bool $otomatis = false): int
    {
        DB::beginTransaction();

        try {
            $jumlah = self::eksekusi($transaksi, $alasan, $otomatis);
            DB::commit();

            return $jumlah;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Batalkan otomatis kalau pesanan memang sudah melewati batas waktu bayar
     * dan member belum upload bukti. Aman dipanggil berkali-kali / bersamaan
     * (baris dikunci dan kondisinya dicek ulang), jadi boleh dipanggil dari
     * command terjadwal maupun dari request member.
     *
     * @return bool true kalau pesanan dibatalkan oleh pemanggilan ini
     */
    public static function batalkanJikaKedaluwarsa(Transaksi $transaksi): bool
    {
        if (! $transaksi->kedaluwarsaBayar()) {
            return false;
        }

        DB::beginTransaction();

        try {
            $terkunci = Transaksi::lockForUpdate()->find($transaksi->id);

            if (! $terkunci || ! $terkunci->kedaluwarsaBayar()) {
                DB::rollBack();

                return false;
            }

            self::eksekusi($terkunci, Transaksi::alasanKedaluwarsaBayar(), true);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membatalkan otomatis pesanan pickup kedaluwarsa', [
                'transaksi_id' => $transaksi->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        $transaksi->refresh();

        try {
            activity()->performedOn($transaksi)
                ->log('membatalkan otomatis pesanan pick up (melebihi batas waktu pembayaran ' . Transaksi::BATAS_BAYAR_MENIT . ' menit)');
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat activity log pembatalan otomatis: ' . $e->getMessage());
        }

        BroadcastHelper::aman(fn () => event(new PesananPickupUpdated($transaksi, 'update', 'batal_otomatis')));

        return true;
    }

    /**
     * Isi pembatalan. Harus dipanggil di dalam transaksi DB.
     */
    private static function eksekusi(Transaksi $transaksi, string $alasan, bool $otomatis): int
    {
        // Stok pesanan baru sudah dikurangi sejak checkout (stok_dikurangi_pada terisi).
        // Pesanan lama baru dikurangi saat prosesPesanan (diproses_pada terisi).
        // Dua-duanya harus dikembalikan; pesanan lama yang belum diproses belum
        // memotong stok, jadi tidak ada yang perlu dikembalikan.
        $perluKembalikanStok = in_array($transaksi->status_pesanan, [
            Transaksi::STATUS_SIAP_DIAMBIL,
            Transaksi::STATUS_DIPROSES,
        ], true) && ($transaksi->stok_dikurangi_pada || $transaksi->diproses_pada);

        if ($perluKembalikanStok) {
            foreach ($transaksi->detailTransaksi as $detail) {
                // Jangan kembalikan stok untuk item hadiah stamp karena hadiahnya tetap aktif berstatus menunggu diambil
                if (str_contains($detail->catatan ?? '', 'Hadiah Stamp') || ((float) $detail->harga_satuan == 0 && str_contains($detail->nama_produk ?? '', 'Hadiah'))) {
                    continue;
                }

                if (! $detail->butuh_produksi && $detail->produk_id) {
                    if ($detail->detail_produk_id) {
                        $varian = DetailProduk::lockForUpdate()->find($detail->detail_produk_id);
                        if ($varian) {
                            $varian->stok = (int) $varian->stok + (int) $detail->jumlah;
                            $varian->status_ketersediaan = $varian->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
                            $varian->save();
                            // Stok & status produk dihitung dari detail produk, jadi cukup menyegarkan cache stoknya.
                            $detail->produk?->segarkanStok();
                        }
                        continue;
                    }

                    try {
                        if ($detail->varian_rasa && $detail->produk?->punya_varian_rasa) {
                            $detail->produk->ubahStokRasa($detail->ukuran, $detail->varian_rasa, $detail->jumlah);
                        } else {
                            $detail->produk?->incrementStokUkuran($detail->ukuran, $detail->jumlah);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengembalikan stok saat membatalkan pesanan pickup', [
                            'produk_id' => $detail->produk_id,
                            'ukuran' => $detail->ukuran,
                            'varian_rasa' => $detail->varian_rasa,
                            'jumlah' => $detail->jumlah,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                }
            }
        }

        // Kembalikan jatah penggunaan voucher member
        VoucherUsage::where('transaksi_id', $transaksi->id)->delete();

        if ($transaksi->voucher_id && $transaksi->voucher) {
            if ($transaksi->voucher instanceof \App\Models\PenukaranPoin) {
                $transaksi->voucher->update(['status' => 'menunggu_diambil', 'diambil_pada' => null, 'transaksi_id' => null]);
            }
        }

        // Kembalikan reward stamp ke status menunggu_diambil (bukan dalam bentuk stamp poin),
        // sehingga kartu hadiah siap diambil kembali via aplikasi (pickup) atau kasir offline.
        $stampRedemptions = StampRedemption::with(['stampProgram', 'produk', 'detailProduk'])
            ->where(function ($q) use ($transaksi) {
                $q->where('transaksi_id', $transaksi->id);
                if (preg_match('/STP-[A-Z0-9]+/', $transaksi->catatan_pesanan ?? '', $matches)) {
                    $q->orWhere('kode_redeem', $matches[0]);
                }
            })
            ->get();

        $penyebab = $otomatis
            ? 'dibatalkan otomatis karena melebihi batas waktu pembayaran'
            : 'dibatalkan oleh toko';

        $jumlahRewardDikembalikan = 0;
        foreach ($stampRedemptions as $redemption) {
            $redemption->update([
                'status' => 'menunggu_diambil',
                'diambil_pada' => null,
                'transaksi_id' => null,
            ]);

            $jumlahRewardDikembalikan++;

            NotificationHelper::untukMember(
                $transaksi->member_id,
                'Pesanan Dibatalkan - Hadiah Stamp Siap Diambil',
                'Pesanan #' . $transaksi->kode_transaksi . ' ' . $penyebab . '. Hadiah ' . ($redemption->produk ? $redemption->nama_tampil : 'stamp') . ' (Kode: ' . $redemption->kode_redeem . ') siap diambil kembali via aplikasi atau kasir.',
                'stamps',
                route('member.stamps.index'),
                'info'
            );
        }

        $transaksi->update([
            'status_pesanan' => Transaksi::STATUS_DIBATALKAN,
            'alasan_pembatalan' => trim($alasan),
        ]);

        return $jumlahRewardDikembalikan;
    }
}