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
     * Data lengkap untuk Tiket Dapur (jenis 'etalase'):
     * 1. 'produksi': Item yang habis / sedang diproduksi / perlu dibuat oleh dapur.
     *    Untuk produk Stok Bersama, yang dibuat adalah produk dasarnya (misal: "Donat Polos").
     * 2. 'pesanan': Rincian seluruh pesanan yang perlu disiapkan/diberi topping,
     *    lengkap dengan variasi/toppingnya (misal: "Donat Polos (Matcha) x2", "Donat Polos (Tiramisu) x2").
     *
     * @return array{produksi: Collection<int, object{nama_produk: string, jumlah: int, catatan: string}>, pesanan: Collection<int, object{nama_produk: string, jumlah: int, catatan: string}>}
     */
    public static function dataTiketDapur(Transaksi $transaksi): array
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

        // Cache pengecekan apakah sebuah produk adalah Stok Bersama yang memiliki varian
        $cacheStokBersama = [];
        $stokBersama = function ($item) use (&$cacheStokBersama): bool {
            if (! $item->produk_id) {
                return false;
            }

            return $cacheStokBersama[$item->produk_id]
                ??= (bool) ($item->produk && $item->produk->is_global && $item->produk->jumlahVarianBersama() > 0);
        };

        $formatCatatan = function ($item) use ($headerBundling, $headerPromoEvent) {
            $catatanPaket = null;
            if ($item->bundling_id && $headerBundling->has($item->bundling_id)) {
                $catatanPaket = $headerBundling->get($item->bundling_id)->catatan;
            } elseif ($item->promo_event_id && $headerPromoEvent->has($item->promo_event_id)) {
                $catatanPaket = $headerPromoEvent->get($item->promo_event_id)->catatan;
            }

            return collect([$catatanPaket, $item->catatan])
                ->filter(fn ($c) => filled($c))
                ->unique()
                ->implode(' | ');
        };

        // Di bagian PERLU DIBUAT: produk stok bersama tidak perlu ada keterangan topping (cuma produk dasarnya).
        $formatNamaProdukProduksi = function ($item) use ($stokBersama) {
            if ($stokBersama($item)) {
                $ukuran = $item->ukuran ?: optional($item->detailProduk)->ukuran;
                return $item->nama_produk . ($ukuran ? " ({$ukuran})" : '');
            }

            $ukuran = $item->ukuran ?: optional($item->detailProduk)->ukuran;
            $rasa = $item->varian_rasa ?: optional($item->detailProduk)->variasi;
            $varian = array_filter([$ukuran, $rasa]);

            return $item->nama_produk . (! empty($varian) ? ' (' . implode(' - ', $varian) . ')' : '');
        };

        $formatNamaProdukPesanan = function ($item) {
            $ukuran = $item->ukuran ?: optional($item->detailProduk)->ukuran;
            $rasa = $item->varian_rasa ?: optional($item->detailProduk)->variasi;
            $varian = array_filter([$ukuran, $rasa]);

            return $item->nama_produk . (! empty($varian) ? ' (' . implode(' - ', $varian) . ')' : '');
        };

        // 1. BAGIAN ATAS: PRODUKSI (Item yang butuh diproduksi / perlu dibuat)
        $produksi = $detail
            ->filter(function ($item) use ($bundlingPunyaIsi, $promoEventPunyaIsi) {
                if ((int) $item->jumlah <= 0 || ! $item->butuh_produksi) {
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
            ->map(function ($item) use ($formatCatatan, $formatNamaProdukProduksi) {
                return (object) [
                    'nama_produk' => $formatNamaProdukProduksi($item),
                    'jumlah' => (int) $item->jumlah,
                    'catatan' => $formatCatatan($item),
                ];
            })
            ->groupBy('nama_produk')
            ->map(fn ($group, $nama) => (object) [
                'nama_produk' => $nama,
                'jumlah' => (int) $group->sum('jumlah'),
                'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
            ])
            ->values();

        // 2. BAGIAN BAWAH: RINCIAN PESANAN (Seluruh item pesanan dengan varian/topping lengkap dipisah)
        $pesanan = $detail
            ->filter(function ($item) use ($bundlingPunyaIsi, $promoEventPunyaIsi) {
                if ((int) $item->jumlah <= 0) {
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
            ->map(function ($item) use ($formatCatatan, $formatNamaProdukPesanan) {
                return (object) [
                    'nama_produk' => $formatNamaProdukPesanan($item),
                    'jumlah' => (int) $item->jumlah,
                    'catatan' => $formatCatatan($item),
                ];
            })
            ->groupBy('nama_produk')
            ->map(fn ($group, $nama) => (object) [
                'nama_produk' => $nama,
                'jumlah' => (int) $group->sum('jumlah'),
                'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
            ])
            ->values();

        return [
            'produksi' => $produksi,
            'pesanan' => $pesanan,
        ];
    }

    /**
     * Daftar seluruh produk pesanan untuk Tiket Dapur (kompatibilitas backwards).
     *
     * @return Collection<int, object{nama_produk: string, jumlah: int, catatan: string}>
     */
    public static function itemsEtalase(Transaksi $transaksi): Collection
    {
        return self::dataTiketDapur($transaksi)['pesanan'];
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
     * Kode sudah dibuat sejak pesanan dibuat (kasir maupun Member App), jadi langsung dicetak
     * begitu struk / tiket dapur dicetak, tanpa menunggu pesanan diambil. Verifikasi kode saat
     * pengambilan tetap dilakukan di halaman Pesanan Pickup (kode yang salah tidak mengubah status).
     * (Nama method dibiarkan supaya tidak perlu mengubah view cetak.)
     */
    public static function kodePickupTerverifikasi(Transaksi $transaksi): ?string
    {
        return filled($transaksi->kode_pickup) ? strtoupper(trim((string) $transaksi->kode_pickup)) : null;
    }
}