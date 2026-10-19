<?php

namespace App\Support;

use App\Models\Transaksi;
use Illuminate\Support\Collection;

/**
 * Kumpulan logika kecil untuk isi struk/tiket cetak (resources/views/pages/transaksi/cetak.blade.php).
 *
 * Dipisah dari view supaya aturan yang sama dipakai persis sama oleh tampilan HTML
 * dan teks yang dikirim ke printer Bluetooth, jadi keduanya tidak bisa beda isi.
 */
class StrukHelper
{
    public const JENIS_PELANGGAN = 'pelanggan';
    public const JENIS_DAPUR = 'dapur';
    /**
     * Nilai internal 'etalase' dipertahankan (supaya tidak bentrok dengan jenis 'dapur'
     * milik Tiket Produksi), tapi namanya di layar/cetakan adalah "Tiket Dapur".
     */
    public const JENIS_ETALASE = 'etalase';

    /** Nama tiket untuk jenis 'etalase' di layar & cetakan. */
    public const LABEL_TIKET_DAPUR = 'Tiket Dapur';

    /**
     * Tiket Produksi (jenis 'dapur') di-HIDE, bukan dihapus: logika cetaknya masih utuh,
     * tombolnya saja yang disembunyikan di halaman Transaksi, Pesanan, dan Histori.
     * Ubah ke true kalau tombol Tiket Produksi mau ditampilkan lagi.
     */
    public const TAMPILKAN_TIKET_PRODUKSI = false;

    /** Jenis cetak yang diizinkan. Nilai lain otomatis jadi struk pelanggan. */
    public const JENIS_VALID = [
        self::JENIS_PELANGGAN,
        self::JENIS_DAPUR,
        self::JENIS_ETALASE,
    ];

    public static function normalisasiJenis(?string $jenis): string
    {
        return in_array($jenis, self::JENIS_VALID, true) ? $jenis : self::JENIS_PELANGGAN;
    }

    /**
     * Daftar produk untuk Tiket Dapur (jenis 'etalase'): SEMUA produk dalam pesanan, tanpa harga,
     * tidak peduli perlu diproduksi atau sudah ada di etalase.
     *
     * - Baris ringkasan paket (bundling/promo event) tidak dicetak, yang dicetak
     *   adalah produk penyusunnya karena itulah yang diambil petugas. Kalau sebuah
     *   paket ternyata tidak punya baris isi, baris paketnya dicetak supaya tidak hilang.
     * - Produk yang sama digabung dan jumlahnya dijumlahkan.
     * - Catatan paket dan catatan per produk digabung (mis. "angetin", "slice").
     * - Produk Stok Bersama yang punya varian: varian TIDAK dipisah (satu baris nama produk saja),
     *   dan hanya yang perlu diproduksi yang dicetak. Yang stoknya masih ada tidak masuk tiket.
     *
     * @return Collection<int, object{nama_produk: string, jumlah: int, catatan: string}>
     */
    public static function itemsEtalase(Transaksi $transaksi): Collection
    {
        $detail = $transaksi->detailTransaksi;

        $headerBundling = $detail
            ->filter(fn ($i) => $i->bundling_id && is_null($i->produk_id))
            ->keyBy('bundling_id');
        $headerPromoEvent = $detail
            ->filter(fn ($i) => $i->promo_event_id && is_null($i->produk_id))
            ->keyBy('promo_event_id');

        $bundlingPunyaIsi = $detail
            ->filter(fn ($i) => $i->bundling_id && $i->produk_id)
            ->pluck('bundling_id')
            ->unique();
        $promoEventPunyaIsi = $detail
            ->filter(fn ($i) => $i->promo_event_id && $i->produk_id)
            ->pluck('promo_event_id')
            ->unique();

        // Produk Stok Bersama yang punya varian (diingat per produk supaya tidak query berulang).
        $cacheStokBersama = [];
        $stokBersama = function ($item) use (&$cacheStokBersama): bool {
            if (! $item->produk_id) {
                return false;
            }

            return $cacheStokBersama[$item->produk_id]
                ??= (bool) ($item->produk && $item->produk->jumlahVarianBersama() > 0);
        };

        return $detail
            ->filter(function ($item) use ($bundlingPunyaIsi, $promoEventPunyaIsi, $stokBersama) {
                if ((int) $item->jumlah <= 0) {
                    return false;
                }

                // Stok Bersama: yang stoknya masih ada (ready) tidak perlu dibuat, jadi tidak masuk tiket.
                if ($stokBersama($item) && ! $item->butuh_produksi) {
                    return false;
                }

                $barisPaket = ($item->bundling_id || $item->promo_event_id) && is_null($item->produk_id);
                if (! $barisPaket) {
                    return true;
                }

                return $item->bundling_id
                    ? ! $bundlingPunyaIsi->contains($item->bundling_id)
                    : ! $promoEventPunyaIsi->contains($item->promo_event_id);
            })
            ->map(function ($item) use ($headerBundling, $headerPromoEvent, $stokBersama) {
                $catatanPaket = null;
                if ($item->bundling_id && $headerBundling->has($item->bundling_id)) {
                    $catatanPaket = $headerBundling->get($item->bundling_id)->catatan;
                } elseif ($item->promo_event_id && $headerPromoEvent->has($item->promo_event_id)) {
                    $catatanPaket = $headerPromoEvent->get($item->promo_event_id)->catatan;
                }

                $ukuran = $item->ukuran ?: optional($item->detailProduk)->ukuran;
                $rasa = $item->varian_rasa ?: optional($item->detailProduk)->variasi;
                $varian = $stokBersama($item) ? [] : array_filter([$ukuran, $rasa]);

                return (object) [
                    'nama_produk' => $item->nama_produk . (! empty($varian) ? ' (' . implode(' - ', $varian) . ')' : ''),
                    'jumlah' => (int) $item->jumlah,
                    'catatan' => collect([$catatanPaket, $item->catatan])
                        ->filter(fn ($c) => filled($c))
                        ->unique()
                        ->implode(' | '),
                ];
            })
            ->groupBy('nama_produk')
            ->map(fn ($group, $nama) => (object) [
                'nama_produk' => $nama,
                'jumlah' => (int) $group->sum('jumlah'),
                'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
            ])
            ->values();
    }

    /**
     * Pecah teks jadi beberapa baris selebar $lebar karakter, memotong di batas
     * KATA (bukan di tengah kata) supaya tiket mudah dibaca. Kata yang lebih
     * panjang dari satu baris terpaksa dipotong.
     *
     * @return string[]
     */
    public static function pecahKata(string $teks, int $lebar): array
    {
        $lebar = max(1, $lebar);
        $baris = [];
        $saatIni = '';

        foreach (preg_split('/\s+/u', trim($teks), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $kata) {
            while (mb_strlen($kata) > $lebar) {
                if ($saatIni !== '') {
                    $baris[] = $saatIni;
                    $saatIni = '';
                }
                $baris[] = mb_substr($kata, 0, $lebar);
                $kata = mb_substr($kata, $lebar);
            }

            $coba = $saatIni === '' ? $kata : $saatIni . ' ' . $kata;
            if (mb_strlen($coba) > $lebar) {
                $baris[] = $saatIni;
                $saatIni = $kata;
            } else {
                $saatIni = $coba;
            }
        }

        if ($saatIni !== '') {
            $baris[] = $saatIni;
        }

        return $baris ?: [''];
    }

    /**
     * Catatan untuk seluruh pesanan: catatan dari pesanan yang ditahan kasir,
     * atau catatan yang diisi member lewat aplikasi.
     */
    public static function catatanPesanan(Transaksi $transaksi): ?string
    {
        $catatan = optional($transaksi->pesananDitahan)->catatan ?: $transaksi->catatan_pesanan;

        return filled($catatan) ? trim((string) $catatan) : null;
    }

    /**
     * Kode pesanan 6 karakter (huruf & angka) yang dicetak di struk pelanggan DAN tiket dapur.
     *
     * - Pesanan kasir langsung (offline): kode selalu ada, jadi langsung dicetak.
     * - Pesanan Member App (online): hanya muncul setelah kasir memasukkan kode itu dan
     *   pesanan resmi ditandai "Sudah Diambil" (kode yang salah tidak pernah mengubah
     *   status), jadi kode di cetakan selalu kode yang sudah terverifikasi.
     */
    public static function kodePickupTerverifikasi(Transaksi $transaksi): ?string
    {
        if ($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP
            && $transaksi->status_pesanan !== Transaksi::STATUS_SUDAH_DIAMBIL) {
            return null;
        }

        return filled($transaksi->kode_pickup) ? strtoupper(trim((string) $transaksi->kode_pickup)) : null;
    }
}