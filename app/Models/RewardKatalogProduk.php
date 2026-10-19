<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardKatalogProduk extends Model
{
    use HasFactory;

    protected $table = 'reward_katalog_produk';

    protected $fillable = [
        'reward_katalog_id',
        'produk_id',
        'detail_produk_id',
        'varian_produk_id',
    ];

    public function rewardKatalog()
    {
        return $this->belongsTo(RewardKatalog::class, 'reward_katalog_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function detailProduk()
    {
        return $this->belongsTo(DetailProduk::class, 'detail_produk_id');
    }

    public function varianProduk()
    {
        return $this->belongsTo(VarianProduk::class, 'varian_produk_id');
    }
}
