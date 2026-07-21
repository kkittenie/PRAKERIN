<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananDitahan extends Model
{
    protected $table = 'pesanan_ditahan';

    protected $fillable = [
        'nama_pembeli',
        'email_pembeli',
        'item_pesanan',
        'subtotal',
        'pajak',
        'diskon',
        'total',
        'catatan',
        'transaksi_id',
    ];

    protected $casts = [
        'item_pesanan' => 'array',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}
