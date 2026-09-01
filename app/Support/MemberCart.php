<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class MemberCart
{
    private const SESSION_KEY = 'member_cart';

    public static function items(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public static function isEmpty(): bool
    {
        return empty(self::items());
    }

    public static function jumlahItem(): int
    {
        return collect(self::items())->sum('jumlah');
    }

    public static function subtotal(): float
    {
        return collect(self::items())->sum(fn ($item) => $item['harga_satuan'] * $item['jumlah']);
    }

    public static function subtotalGross(): float
    {
        return collect(self::items())->sum(function ($item) {
            $hargaAsal = (float) ($item['harga_asal'] ?? $item['harga_satuan']);
            return $hargaAsal * $item['jumlah'];
        });
    }

    public static function totalDiskonProduk(): float
    {
        return collect(self::items())->sum(function ($item) {
            $diskonProdukSatuan = (float) ($item['diskon_produk_satuan'] ?? 0);
            return $diskonProdukSatuan * $item['jumlah'];
        });
    }

    /**
     * Coret diskon promo produk yang syarat "minimal pembelian"-nya belum
     * kepenuhi oleh subtotal keranjang saat ini.
     *
     * Diskon per-item awalnya dihitung & dikunci saat produk ditambahkan ke
     * keranjang (lihat KeranjangController::tambah()/perbarui()), padahal
     * min_pembelian itu syarat TOTAL keranjang — yang baru pasti nilainya
     * begitu isi keranjang lengkap/berubah. Makanya harus di-recheck ulang
     * di sini setiap kali keranjang mau ditampilkan/di-checkout, bukan
     * cukup dicek sekali pas nambah item.
     *
     * Dipakai subtotalGross (total harga ASLI, sebelum diskon promo apapun)
     * sebagai basis pembanding, biar gak sirkular (gak kepengaruh sama
     * diskon yang lagi mau kita tentuin sendiri) — sama kayak basis yang
     * dipakai buat ngecek min_pembelian voucher reward di CheckoutController.
     */
    public static function terapkanMinimalPembelian(): void
    {
        $items = self::items();
        if (empty($items)) {
            return;
        }

        $subtotalGross = self::subtotalGross();
        $berubah = false;

        foreach ($items as &$item) {
            $minPembelian = (float) ($item['min_pembelian_promo'] ?? 0);
            $diskonProdukSatuan = (float) ($item['diskon_produk_satuan'] ?? 0);

            if ($minPembelian > 0 && $diskonProdukSatuan > 0 && $minPembelian > $subtotalGross) {
                $item['harga_satuan'] = (float) ($item['harga_asal'] ?? $item['harga_satuan']);
                $item['diskon_produk_satuan'] = 0;
                $item['nama_promo'] = null;
                $item['jenis_promo'] = null;
                $item['persentase_promo'] = null;
                $berubah = true;
            }
        }
        unset($item);

        if ($berubah) {
            Session::put(self::SESSION_KEY, array_values($items));
        }
    }

    public static function tambah(array $data): void
    {
        $data['produk_id'] = $data['produk_id'] ?? null;
        $data['bundling_id'] = $data['bundling_id'] ?? null;
        $data['promo_event_id'] = $data['promo_event_id'] ?? null;

        $items = self::items();

        $existingKey = null;
        foreach ($items as $key => $item) {
            if (
                ($item['produk_id'] ?? null) === $data['produk_id']
                && ($item['bundling_id'] ?? null) === $data['bundling_id']
                && ($item['promo_event_id'] ?? null) === $data['promo_event_id']
                && ($item['ukuran'] ?? null) === ($data['ukuran'] ?? null)
                && ($item['catatan'] ?? null) === ($data['catatan'] ?? null)
            ) {
                $existingKey = $key;
                break;
            }
        }

        if ($existingKey !== null) {
            $items[$existingKey]['jumlah'] += $data['jumlah'];
        } else {
            $data['id'] = (string) Str::uuid();
            $items[] = $data;
        }

        Session::put(self::SESSION_KEY, array_values($items));
    }

    /**
     * Perbarui item yang sudah ada di keranjang (dipakai saat member membuka
     * ulang halaman produk/bundling lewat "Change" di checkout). Berbeda
     * dengan tambah(), ini tidak digabung dengan item lain — id item tetap
     * sama, cuma field-nya yang ditimpa.
     */
    public static function perbarui(string $itemId, array $data): void
    {
        $items = self::items();

        foreach ($items as &$item) {
            if ($item['id'] === $itemId) {
                $item = array_merge($item, $data);
                break;
            }
        }
        unset($item);

        Session::put(self::SESSION_KEY, array_values($items));
    }

    public static function cariItem(string $itemId): ?array
    {
        foreach (self::items() as $item) {
            if ($item['id'] === $itemId) {
                return $item;
            }
        }

        return null;
    }

    public static function ubahJumlah(string $itemId, int $jumlah): void
    {
        $items = self::items();

        if ($jumlah <= 0) {
            $items = array_filter($items, fn ($item) => $item['id'] !== $itemId);
        } else {
            foreach ($items as &$item) {
                if ($item['id'] === $itemId) {
                    $item['jumlah'] = $jumlah;
                }
            }
            unset($item);
        }

        Session::put(self::SESSION_KEY, array_values($items));
    }

    public static function hapus(string $itemId): void
    {
        $items = array_values(array_filter(
            self::items(),
            fn ($item) => $item['id'] !== $itemId
        ));

        Session::put(self::SESSION_KEY, $items);
    }

    public static function kosongkan(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}