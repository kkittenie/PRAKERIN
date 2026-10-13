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

    /**
     * Baris pembatasan ukuran per-produk buat promo ini. Satu promo bisa
     * kepasang ke beberapa produk (lewat produks()), dan tiap produk itu
     * punya checklist ukurannya sendiri-sendiri lewat tabel ini —
     * bukan 1 checklist global yang berlaku sama buat semua produk.
     */
    public function produkUkuranPivots()
    {
        return $this->hasMany(PromoProdukUkuran::class, 'promo_id');
    }

    /**
     * Baris pembatasan rasa per-produk buat promo ini.
     */
    public function produkRasaPivots()
    {
        return $this->hasMany(PromoProdukRasa::class, 'promo_id');
    }

    /**
     * ID ukuran yang dibatasi buat promo ini, KHUSUS produk tertentu.
     * Kosong = gak dibatasi (berlaku ke semua ukuran produk itu) — sama
     * kayak sebelum fitur ini ada.
     */
    public function ukuranIdsUntukProduk(int $produkId): \Illuminate\Support\Collection
    {
        if ($this->relationLoaded('produkUkuranPivots')) {
            return $this->produkUkuranPivots
                ->where('produk_id', $produkId)
                ->pluck('ukuran_id')
                ->values();
        }

        return $this->produkUkuranPivots()
            ->where('produk_id', $produkId)
            ->pluck('ukuran_id');
    }

    public function berlakuUntukUkuran(int $produkId, ?int $ukuranId): bool
    {
        $daftar = $this->ukuranIdsUntukProduk($produkId);
        if ($daftar->isEmpty()) {
            return true;
        }

        return $ukuranId !== null && $daftar->contains($ukuranId);
    }

    /**
     * ID varian rasa yang dibatasi buat promo ini, KHUSUS produk tertentu.
     * Kosong = gak dibatasi (berlaku ke semua rasa produk itu).
     */
    public function rasaIdsUntukProduk(int $produkId): \Illuminate\Support\Collection
    {
        if ($this->relationLoaded('produkRasaPivots')) {
            return $this->produkRasaPivots
                ->where('produk_id', $produkId)
                ->pluck('varian_rasa_id')
                ->values();
        }

        return $this->produkRasaPivots()
            ->where('produk_id', $produkId)
            ->pluck('varian_rasa_id');
    }

    public function berlakuUntukRasa(int $produkId, ?int $rasaId): bool
    {
        $daftar = $this->rasaIdsUntukProduk($produkId);
        if ($daftar->isEmpty()) {
            return true;
        }

        return $rasaId !== null && $daftar->contains($rasaId);
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