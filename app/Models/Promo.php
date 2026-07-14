<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promos';

    protected $fillable = [
        'nama_promo',
        'persentase',
        'mulai_berlaku',
        'selesai_berlaku',
    ];

    protected $casts = [
        'mulai_berlaku' => 'datetime',
        'selesai_berlaku' => 'datetime',
        'persentase' => 'float',
    ];

    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'promo_produk', 'promo_id', 'produk_id')->withTimestamps();
    }
}
