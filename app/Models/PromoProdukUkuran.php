<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoProdukUkuran extends Model
{
    protected $table = 'promo_produk_ukurans';

    protected $fillable = [
        'promo_id',
        'produk_id',
        'ukuran_id',
    ];

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function ukuran()
    {
        return $this->belongsTo(Ukuran::class);
    }
}