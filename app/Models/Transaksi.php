<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'kode_transaksi',
        'nama_pembeli',
        'subtotal',
        'pajak',
        'diskon',
        'total_bayar',
        'jumlah_diterima',
        'kembalian',
        'metode_pembayaran',
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }
}
