<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrisImage extends Model
{
    protected $table = 'qris_images';

    protected $fillable = ['kode_qris', 'nama', 'gambar', 'status_aktif'];

    protected $casts = ['status_aktif' => 'boolean'];

    /** Kode QRIS (QRS001, dst). Kalau kolomnya masih kosong, dibentuk dari id. */
    public function getKodeQrisAttribute($value): string
    {
        if (! empty($value)) {
            return $value;
        }

        return 'QRS' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    /** URL gambar QRIS yang sedang aktif; kalau belum ada, pakai placeholder bawaan. */
    public static function urlAktif(): string
    {
        $aktif = static::where('status_aktif', true)->latest('id')->first();

        return $aktif && $aktif->gambar
            ? asset('storage/' . $aktif->gambar)
            : asset('images/payment/qris-placeholder.png');
    }
}