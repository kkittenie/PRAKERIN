<?php

namespace App\Support;

use App\Models\PengaturanJamOperasional;
use Illuminate\Support\Carbon;

/**
 * Jam operasional toko untuk pemesanan lewat Member App.
 * Di luar jam ini member tidak bisa menambah keranjang maupun checkout.
 * Memakai timezone aplikasi (Asia/Jakarta).
 *
 * Jam buka & tutup dibaca dari tabel `pengaturan_jam_operasional` sehingga
 * bisa diubah dari panel admin tanpa deploy ulang.
 */
class JamOperasional
{
    /** Cache per-request supaya tidak query berulang. */
    protected static ?PengaturanJamOperasional $cache = null;

    /**
     * Ambil pengaturan dari database (di-cache per-request).
     */
    protected static function pengaturan(): PengaturanJamOperasional
    {
        return self::$cache ??= PengaturanJamOperasional::ambil();
    }

    /**
     * Jam buka sebagai string (misal "06:00").
     */
    public static function buka(): string
    {
        return self::pengaturan()->jam_buka;
    }

    /**
     * Jam tutup sebagai string (misal "20:00").
     */
    public static function tutup(): string
    {
        return self::pengaturan()->jam_tutup;
    }

    public static function sedangBuka(?Carbon $waktu = null): bool
    {
        $waktu = $waktu ?? now();
        $menit = $waktu->hour * 60 + $waktu->minute;

        return $menit >= self::keMenit(self::buka()) && $menit < self::keMenit(self::tutup());
    }

    public static function pesanTutup(): string
    {
        return 'Maaf, toko sedang tutup. Pemesanan hanya bisa dilakukan pada jam operasional '
            . self::buka() . ' - ' . self::tutup() . ' WIB.';
    }

    /**
     * Reset cache (berguna untuk testing atau jika jam diubah
     * di tengah request yang sama).
     */
    public static function resetCache(): void
    {
        self::$cache = null;
    }

    private static function keMenit(string $jam): int
    {
        [$h, $m] = array_map('intval', explode(':', $jam));

        return $h * 60 + $m;
    }
}