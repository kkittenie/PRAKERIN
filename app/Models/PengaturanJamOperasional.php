<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class PengaturanJamOperasional extends Model
{
    protected $table = 'pengaturan_jam_operasional';

    protected $fillable = [
        'jam_buka',
        'jam_tutup',
    ];

    /**
     * Satu-satunya baris pengaturan. Kalau belum ada (baris kosong atau
     * migration belum dijalankan), dibuat sementara di memori dengan jam bawaan.
     */
    public static function ambil(): self
    {
        $bawaan = new self([
            'jam_buka' => '06:00',
            'jam_tutup' => '20:00',
        ]);

        try {
            return self::query()->first() ?? $bawaan;
        } catch (QueryException $e) {
            // Tabel belum ada (migration belum dijalankan): pakai jam bawaan.
            return $bawaan;
        }
    }
}