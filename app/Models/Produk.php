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
        $isPromoBerlaku = function ($promo) use ($now) {
            if (!$promo->status_aktif) return false;
            if ($promo->mulai_berlaku > $now) return false;
            if ($promo->selesai_berlaku && $promo->selesai_berlaku < $now) return false;
            return true;
        };

        if ($this->relationLoaded('promos')) {
            return $this->promos->filter($isPromoBerlaku)->sortByDesc(function ($p) {
                return $p->jenis_potongan === 'nominal'
                    ? $p->nominal_potongan
                    : (($p->persentase ?? 0) / 100 * $this->harga_jual);
            })->first();
        }

        return $this->promos()
            ->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            })
            ->get()
            ->sortByDesc(function ($p) {
                return $p->jenis_potongan === 'nominal'
                    ? $p->nominal_potongan
                    : (($p->persentase ?? 0) / 100 * $this->harga_jual);
            })
            ->first();
    }

    public function getHargaDiskonAttribute()
    {
        $promo = $this->promo_aktif;
        if ($promo) {
            if ($promo->jenis_potongan === 'nominal') {
                $diskon = $promo->nominal_potongan;
            } else {
                $diskon = (($promo->persentase ?? 0) / 100) * $this->harga_jual;
            }
            return max(0, $this->harga_jual - $diskon);
        }
        return $this->harga_jual;
    }
}
