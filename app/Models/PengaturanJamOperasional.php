<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanJamOperasional extends Model
{
    protected $table = 'pengaturan_jam_operasional';

    protected $fillable = [
        'jam_buka',
        'jam_tutup',
    ];

    /**
     * Satu-satunya baris pengaturan. Kalau belum ada (misalnya migration
     * belum dijalankan), dibuat sementara di memori dengan jam bawaan.
     */
    public static function ambil(): self
    {
        return self::query()->first() ?? new self([
            'jam_buka' => '06:00',
            'jam_tutup' => '20:00',
        ]);
    }
}