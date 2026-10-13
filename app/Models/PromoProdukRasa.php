<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoProdukRasa extends Model
{
    protected $table = 'promo_produk_rasas';

    protected $fillable = [
        'promo_id',
        'produk_id',
        'varian_rasa_id',
    ];

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function varianRasa()
    {
        return $this->belongsTo(VarianRasa::class, 'varian_rasa_id');
    }
}
