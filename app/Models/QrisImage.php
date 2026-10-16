<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrisImage extends Model
{
    protected $table = 'qris_images';

    protected $fillable = ['nama', 'gambar', 'status_aktif'];

    protected $casts = ['status_aktif' => 'boolean'];

    /** URL gambar QRIS yang sedang aktif; kalau belum ada, pakai placeholder bawaan. */
    public static function urlAktif(): string
    {
        $aktif = static::where('status_aktif', true)->latest('id')->first();

        return $aktif && $aktif->gambar
            ? asset('storage/' . $aktif->gambar)
            : asset('images/payment/qris-placeholder.png');
    }
}