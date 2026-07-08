<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiBahanBaku extends Model
{
    protected $table = 'produksi_bahan_bakus';

    protected $fillable = [
        'produksi_id',
        'bahan_baku_id',
        'jumlah_terpakai',
    ];

    public function produksi()
    {
        return $this->belongsTo(Produksi::class, 'produksi_id');
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
