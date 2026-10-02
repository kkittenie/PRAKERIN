<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukUkuranRasa extends Model
{
    protected $table = 'produk_ukuran_rasas';

    protected $fillable = [
        'produk_id',
        'ukuran_id',
        'varian_rasa_id',
        'stok',
        'stok_minimum',
        'status_ketersediaan',
    ];

    protected $casts = [
        'stok' => 'integer',
        'stok_minimum' => 'integer',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function ukuran()
    {
        return $this->belongsTo(Ukuran::class, 'ukuran_id');
    }

    public function varianRasa()
    {
        return $this->belongsTo(VarianRasa::class, 'varian_rasa_id');
    }
}