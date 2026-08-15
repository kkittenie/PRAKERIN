<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanPajak extends Model
{
    protected $table = 'pengaturan_pajak';

    protected $fillable = [
        'nama_pajak',
        'persentase',
        'status_aktif',
        'catatan',
    ];

    protected $casts = [
        'persentase' => 'float',
        'status_aktif' => 'boolean',
    ];

    /**
     * Ambil data pengaturan pajak tunggal (singleton), jika belum ada buat default.
     */
    public static function getAktif()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'nama_pajak' => 'PB1 / Pajak Restoran',
                'persentase' => 10.00,
                'status_aktif' => true,
                'catatan' => 'Pajak restoran yang berlaku untuk setiap transaksi.',
            ]
        );
    }
}
