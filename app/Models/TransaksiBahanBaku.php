<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiBahanBaku extends Model
{
    protected $table = 'transaksi_bahan_bakus';

    protected $fillable = [
        'tanggal',
        'bahan_baku_id',
        'jumlah',
        'harga_satuan',
        'total_harga',
        'supplier',
        'nomor_nota',
        'user_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(
            BahanBaku::class,
            'bahan_baku_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}