<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Jam operasional toko untuk pemesanan lewat Member App.
 * Di luar jam ini member tidak bisa menambah keranjang maupun checkout.
 * Memakai timezone aplikasi (Asia/Jakarta).
 */
class JamOperasional
{
    public const BUKA = '06:00';
    public const TUTUP = '20:00';

    /** Buka mulai 06:00 (inklusif) sampai sebelum 20:00. */
    public static function sedangBuka(?Carbon $waktu = null): bool
    {
        $waktu = $waktu ?? now();
        $menit = $waktu->hour * 60 + $waktu->minute;

        return $menit >= self::keMenit(self::BUKA) && $menit < self::keMenit(self::TUTUP);
    }

    public static function pesanTutup(): string
    {
        return 'Maaf, toko sedang tutup. Pemesanan hanya bisa dilakukan pada jam operasional ' . self::BUKA . ' - ' . self::TUTUP . ' WIB.';
    }

    private static function keMenit(string $jam): int
    {
        [$h, $m] = array_map('intval', explode(':', $jam));

        return $h * 60 + $m;
    }
}