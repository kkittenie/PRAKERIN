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
        'voucher_id',
        'poin_sebelum',
        'poin_didapat',
        'subtotal',
        'pajak',
        'diskon',
        'diskon_persen',
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

    public function voucher()
    {
        return $this->belongsTo(PenukaranPoin::class, 'voucher_id');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }

    public function pesananDitahan()
    {
        return $this->hasOne(PesananDitahan::class, 'transaksi_id');
    }

    public function stampRedemptions()
    {
        return $this->hasMany(StampRedemption::class, 'transaksi_id');
    }
}