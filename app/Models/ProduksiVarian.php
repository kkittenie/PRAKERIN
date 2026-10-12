<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiVarian extends Model
{
    protected $table = 'produksi_varians';

    protected $fillable = [
        'produksi_id',
        'produk_varian_id',
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

    public function produkVarian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }

    public function getNamaLengkapAttribute(): string
    {
        return collect([$this->ukuran, $this->variasi])->filter()->implode(' / ') ?: 'Standar';
    }
}