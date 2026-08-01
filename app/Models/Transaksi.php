<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'kode_transaksi',
        'nama_pembeli',
        'member_id',
        'subtotal',
        'pajak',
        'diskon',
        'diskon_label',
        'total_bayar',
        'jumlah_diterima',
        'kembalian',
        'metode_pembayaran',
        'status_pembayaran',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }

    public function pesananDitahan()
    {
        return $this->hasOne(PesananDitahan::class, 'transaksi_id');
    }
}
