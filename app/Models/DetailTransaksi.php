<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'produk_varian_id',
        'bundling_id',
        'promo_event_id',
        'nama_produk',
        'ukuran',
        'varian_rasa',
        'kode_barcode',
        'harga_satuan',
        'jumlah',
        'catatan',
        'subtotal',
        'butuh_produksi',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function detailProduk()
    {
        return $this->belongsTo(DetailProduk::class, 'produk_varian_id');
    }

    /** @deprecated Nama lama dari detailProduk(). */
    public function produkVarian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }
}