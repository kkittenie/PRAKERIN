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

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

    // public function produksi()
    // {
    //     return $this->hasMany(Produksi::class, 'produk_id');
    // }
}
