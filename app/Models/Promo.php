<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promos';

    protected $fillable = [
        'nama_promo',
        'jenis_potongan',
        'persentase',
        'nominal_potongan',
        'min_pembelian',
        'status_aktif',
        'mulai_berlaku',
        'selesai_berlaku',
    ];

    protected $casts = [
        'mulai_berlaku' => 'datetime',
        'selesai_berlaku' => 'datetime',
        'persentase' => 'float',
        'nominal_potongan' => 'float',
        'min_pembelian' => 'float',
        'status_aktif' => 'boolean',
    ];

    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'promo_produk', 'promo_id', 'produk_id')->withTimestamps();
    }

    public function scopeBerlaku($query)
    {
        $now = now();
        return $query->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            });
    }
}

