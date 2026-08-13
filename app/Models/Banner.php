<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'judul',
        'gambar',
        'urutan',
        'status_aktif',
        'tanggal_mulai_tampil',
        'tanggal_selesai_tampil',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'tanggal_mulai_tampil' => 'date',
        'tanggal_selesai_tampil' => 'date',
    ];

    // Scope untuk ambil banner yang sedang aktif tampil (dipakai nanti di API carousel mobile)
    public function scopeTampil($query)
    {
        $now = now();
        return $query->where('status_aktif', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('tanggal_mulai_tampil')
                  ->orWhere('tanggal_mulai_tampil', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('tanggal_selesai_tampil')
                  ->orWhere('tanggal_selesai_tampil', '>=', $now);
            });
    }
}