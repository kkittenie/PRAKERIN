<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundlingItem extends Model
{
    protected $table = 'bundling_items';

    protected $fillable = [
        'bundling_id',
        'produk_id',
        'detail_produk_id',
        'ukuran',
        'varian_rasa',
        'qty',
    ];

    public function bundling()
    {
        return $this->belongsTo(Bundling::class, 'bundling_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function detailProduk()
    {
        return $this->belongsTo(DetailProduk::class, 'detail_produk_id');
    }
}