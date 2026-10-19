<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rincian produksi per detail produk. Kode, ukuran, variasi, dan harga disalin saat
 * produksi dicatat, jadi riwayat tetap terbaca walau detail produknya diubah atau dihapus.
 */
class ProduksiVarian extends Model
{
    protected $table = 'produksi_varians';

    protected $fillable = [
        'produksi_id',
        'detail_produk_id',
        'varian_produk_id',
        'ukuran',
        'variasi',
        'kode_barcode',
        'harga_jual',
        'jumlah_hasil',
        'kebutuhan',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'jumlah_hasil' => 'integer',
        'kebutuhan' => 'integer',
    ];

    public function produksi()
    {
        return $this->belongsTo(Produksi::class, 'produksi_id');
    }

    public function detailProduk()
    {
        return $this->belongsTo(DetailProduk::class, 'detail_produk_id');
    }

    public function varianProduk()
    {
        return $this->belongsTo(VarianProduk::class, 'varian_produk_id');
    }

    public function getNamaLengkapAttribute(): string
    {
        return collect([$this->ukuran, $this->variasi])->filter()->implode(' / ') ?: 'Standar';
    }
}