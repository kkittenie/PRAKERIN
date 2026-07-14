<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';

    protected $fillable = [
        'kategori_id',
        'kode_produk',
        'nama_produk',
        'harga_jual',
        'harga_modal',
        'stok',
        'stok_minimum',
        'foto',
        'status_aktif',
        'status_ketersediaan',
    ];

    protected $appends = [
        'promo_aktif',
        'harga_diskon'
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

    public function produksi()
    {
        return $this->hasMany(Produksi::class);
    }

    public function promos()
    {
        return $this->belongsToMany(Promo::class, 'promo_produk', 'produk_id', 'promo_id')->withTimestamps();
    }

    public function getPromoAktifAttribute()
    {
        $now = now();
        if ($this->relationLoaded('promos')) {
            return $this->promos->first(function ($promo) use ($now) {
                return $promo->mulai_berlaku <= $now && $promo->selesai_berlaku >= $now;
            });
        }

        return $this->promos()
            ->where('mulai_berlaku', '<=', $now)
            ->where('selesai_berlaku', '>=', $now)
            ->first();
    }

    public function getHargaDiskonAttribute()
    {
        $promo = $this->promo_aktif;
        if ($promo) {
            $diskon = ($promo->persentase / 100) * $this->harga_jual;
            return max(0, $this->harga_jual - $diskon);
        }
        return $this->harga_jual;
    }
}
