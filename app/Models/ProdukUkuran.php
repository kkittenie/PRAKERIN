<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukUkuran extends Model
{
    protected $table = 'produk_ukurans';

    protected $fillable = [
        'produk_id',
        'ukuran_id',
        'harga_modal',
        'harga_jual',
        'stok',
        'stok_minimum',
        'status_ketersediaan',
        'status_aktif',
    ];

    protected $casts = [
        'harga_modal' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'status_aktif' => 'boolean',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function ukuran()
    {
        return $this->belongsTo(Ukuran::class, 'ukuran_id');
    }

    public function getPerluDiproduksiAttribute(): bool
    {
        return $this->status_ketersediaan === 'perlu_diproduksi' || $this->stok <= 0;
    }
}