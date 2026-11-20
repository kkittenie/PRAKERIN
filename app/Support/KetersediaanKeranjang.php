<?php

namespace App\Support;

use App\Models\Bundling;
use App\Models\DetailProduk;
use App\Models\Produk;
use App\Models\PromoProdukEvent;

/**
 * Cek ulang ketersediaan stok isi keranjang member (read-only, tanpa mengubah
 * data apa pun). Stok bisa saja habis di antara saat item dimasukkan ke
 * keranjang dan saat member membuka/menekan tombol checkout.
 *
 * Dipakai halaman checkout untuk menampilkan peringatan + menonaktifkan tombol
 * "Order Now". Pengecekan yang menentukan tetap dilakukan di
 * CheckoutController::store (di dalam transaksi DB, dengan baris terkunci).
 */
class KetersediaanKeranjang
{
    /**
     * @param  array<int, array<string, mixed>>  $items  isi MemberCart::items()
     * @return array<int, string> daftar pesan masalah stok (kosong = semua aman)
     */
    public static function periksa(array $items): array
    {
        $pesan = [];
        $terpakai = [];          // alokasi stok produk tanpa detail produk (Produk::alokasiStok)
        $terpakaiVarian = [];    // pemakaian stok per detail produk (varian)
        $terpakaiBundling = [];
        $terpakaiPromoEvent = [];

        foreach ($items as $item) {
            // Hadiah stamp stoknya sudah dipotong saat hadiah ditukar.
            if (! empty($item['is_reward_stamp'])) {
                continue;
            }

            $jumlah = max(1, (int) ($item['jumlah'] ?? 1));

            if (! empty($item['bundling_id'])) {
                $bundling = Bundling::with('items.produk', 'items.detailProduk')->find($item['bundling_id']);
                if (! $bundling) {
                    $pesan[] = 'Bundling package in your cart is no longer available.';
                    continue;
                }

                $maks = $bundling->maksPaketTersedia($item['pilihan_khusus'] ?? []);
                if ($maks === null) {
                    continue;
                }

                $sudah = $bundling->isBebas() ? 0 : (int) ($terpakaiBundling[$bundling->id] ?? 0);
                if ($sudah + $jumlah > $maks) {
                    $pesan[] = self::susunPesan($bundling->nama_bundling, max(0, $maks - $sudah), $jumlah, 'packages');
                }
                $terpakaiBundling[$bundling->id] = $sudah + $jumlah;
                continue;
            }

            if (! empty($item['promo_event_id'])) {
                $promoEvent = PromoProdukEvent::find($item['promo_event_id']);
                if (! $promoEvent) {
                    $pesan[] = 'Promo event in your cart is no longer available.';
                    continue;
                }

                $sudah = (int) ($terpakaiPromoEvent[$promoEvent->id] ?? 0);
                $sisa = max(0, (int) $promoEvent->stok_tersedia - $sudah);
                if ($sisa < $jumlah) {
                    $pesan[] = self::susunPesan($promoEvent->nama_event, $sisa, $jumlah, 'packages');
                }
                $terpakaiPromoEvent[$promoEvent->id] = $sudah + $jumlah;
                continue;
            }

            $produk = Produk::find($item['produk_id'] ?? null);
            if (! $produk) {
                $pesan[] = 'A product in your cart is no longer available.';
                continue;
            }

            $ukuran = $item['ukuran'] ?? null;
            $varianRasa = $item['varian_rasa'] ?? null;
            $label = trim(implode(', ', array_filter([$ukuran, $varianRasa])));
            $nama = $label !== '' ? "{$produk->nama_produk} ({$label})" : $produk->nama_produk;

            // Cara menentukan varian sama dengan CheckoutController::store.
            $varian = null;
            if (! empty($item['detail_produk_id'])) {
                $varian = DetailProduk::find($item['detail_produk_id']);
            } elseif ($produk->detailProduks()->exists()) {
                $varian = $produk->detailProduks()
                    ->where(function ($q) use ($ukuran, $varianRasa) {
                        if ($ukuran) {
                            $q->where('ukuran', $ukuran);
                        }
                        if ($varianRasa) {
                            $q->where('variasi', $varianRasa);
                        }
                    })->first();
            }

            if ($varian) {
                $sudah = (int) ($terpakaiVarian[$varian->id] ?? 0);
                $sisa = $varian->status_aktif ? max(0, (int) $varian->stok - $sudah) : 0;
                if ($sisa < $jumlah) {
                    $pesan[] = self::susunPesan($nama, $sisa, $jumlah, 'pcs');
                }
                $terpakaiVarian[$varian->id] = $sudah + $jumlah;
                continue;
            }

            $alokasi = $produk->alokasiStok($ukuran, $varianRasa ? [$varianRasa] : [], $jumlah, $terpakai);
            if ($alokasi['produksi'] > 0) {
                $sisa = $jumlah - (int) $alokasi['produksi'];
                $pesan[] = self::susunPesan($nama, max(0, $sisa), $jumlah, 'pcs');
            }
        }

        return array_values(array_unique($pesan));
    }

    private static function susunPesan(string $nama, int $sisa, int $diminta, string $satuan): string
    {
        if ($sisa <= 0) {
            return "{$nama} is out of stock.";
        }

        return "Only {$sisa} {$satuan} of {$nama} remaining, but you have {$diminta} in your cart.";
    }
}