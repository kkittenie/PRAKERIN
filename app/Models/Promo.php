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
     * Detail produk (kombinasi ukuran + variasi) yang dipilih buat promo ini.
     * Satu promo bisa kepasang ke beberapa produk (lewat produks()), dan tiap
     * produk punya pilihan detailnya sendiri-sendiri. Produk yang tidak punya
     * baris di tabel ini = promo berlaku ke SEMUA detail produk itu.
     */
    public function detailProduks()
    {
        return $this->belongsToMany(DetailProduk::class, 'promo_detail_produks', 'promo_id', 'detail_produk_id')->withTimestamps();
    }

    public function varianProduks()
    {
        return $this->belongsToMany(VarianProduk::class, 'promo_detail_produks', 'promo_id', 'varian_produk_id')->withTimestamps();
    }

    /**
     * ID detail produk yang dibatasi buat promo ini, KHUSUS produk tertentu.
     * Kosong = gak dibatasi (berlaku ke semua detail produk itu).
     */
    public function detailIdsUntukProduk(int $produkId): \Illuminate\Support\Collection
    {
        $detail = $this->relationLoaded('detailProduks')
            ? $this->detailProduks
            : $this->detailProduks()->get();

        return $detail
            ->where('produk_id', $produkId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    public function varianIdsUntukProduk(int $produkId): \Illuminate\Support\Collection
    {
        $varian = $this->relationLoaded('varianProduks')
            ? $this->varianProduks
            : $this->varianProduks()->get();

        return $varian
            ->where('produk_id', $produkId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    /** Apakah promo ini berlaku untuk detail produk tersebut (milik produk $produkId). */
    public function berlakuUntukDetail(int $produkId, ?DetailProduk $detail, ?VarianProduk $varianProduk = null): bool
    {
        $daftarDetail = $this->detailIdsUntukProduk($produkId);
        $daftarVarian = $this->varianIdsUntukProduk($produkId);

        if ($daftarDetail->isEmpty() && $daftarVarian->isEmpty()) {
            return true;
        }

        if ($varianProduk !== null) {
            return $daftarVarian->isEmpty() || $daftarVarian->contains((int) $varianProduk->id);
        }

        return $detail !== null && $daftarDetail->contains((int) $detail->id);
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