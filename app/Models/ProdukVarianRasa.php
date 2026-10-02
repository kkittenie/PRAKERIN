<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukVarianRasa extends Model
{
    protected $table = 'produk_varian_rasas';

    protected $fillable = [
        'produk_id',
        'varian_rasa_id',
        'stok',
        'stok_minimum',
        'status_ketersediaan',
        'status_aktif',
    ];

    protected $casts = [
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'status_aktif' => 'boolean',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function varianRasa()
    {
        return $this->belongsTo(VarianRasa::class, 'varian_rasa_id');
    }

    public function getPerluDiproduksiAttribute(): bool
    {
        return $this->status_ketersediaan === 'perlu_diproduksi' || $this->stok <= 0;
    }
}
