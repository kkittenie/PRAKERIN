<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WelcomeSlide extends Model
{
    protected $table = 'welcome_slides';

    protected $fillable = ['judul', 'deskripsi', 'gambar', 'urutan', 'status_aktif'];

    protected $casts = ['status_aktif' => 'boolean'];

    /** URL gambar slide aktif pertama (dipakai halaman login); $default kalau belum ada slide. */
    public static function urlPertama(string $default): string
    {
        $slide = static::tampil()->first();

        return $slide ? asset('storage/' . $slide->gambar) : $default;
    }

    /** URL gambar slide aktif terakhir (dipakai halaman register); $default kalau belum ada slide. */
    public static function urlTerakhir(string $default): string
    {
        $slide = static::tampil()->get()->last();

        return $slide ? asset('storage/' . $slide->gambar) : $default;
    }

    /** Slide aktif, urut sesuai pengaturan admin (angka kecil tampil lebih dulu). */
    public function scopeTampil($query)
    {
        return $query->where('status_aktif', true)->orderBy('urutan')->orderBy('id');
    }
}