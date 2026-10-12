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
}