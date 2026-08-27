<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promos';

    protected $fillable = [
        'nama_promo',
        'jenis_potongan',
        'persentase',
        'nominal_potongan',
        'min_pembelian',
        'status_aktif',
        'mulai_berlaku',
        'selesai_berlaku',
    ];

    protected $casts = [
        'mulai_berlaku' => 'datetime',
        'selesai_berlaku' => 'datetime',
        'persentase' => 'float',
        'nominal_potongan' => 'float',
        'min_pembelian' => 'float',
        'status_aktif' => 'boolean',
    ];

    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'promo_produk', 'promo_id', 'produk_id')->withTimestamps();
    }

    public function ukurans()
    {
        return $this->belongsToMany(Ukuran::class, 'promo_ukurans', 'promo_id', 'ukuran_id')->withTimestamps();
    }

    /**
     * ID ukuran yang dibatasi buat promo ini. Kosong = gak dibatasi
     * (berlaku ke semua ukuran) — sama kayak sebelum fitur ini ada.
     */
    public function ukuranIdsTerbatas(): \Illuminate\Support\Collection
    {
        return $this->relationLoaded('ukurans')
            ? $this->ukurans->pluck('id')
            : $this->ukurans()->pluck('ukurans.id');
    }

    public function berlakuUntukUkuran(?int $ukuranId): bool
    {
        $daftar = $this->ukuranIdsTerbatas();
        if ($daftar->isEmpty()) {
            return true;
        }

        return $ukuranId !== null && $daftar->contains($ukuranId);
    }

    public function scopeBerlaku($query)
    {
        $now = now();
        return $query->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            });
    }
}