<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_bakus';

    protected $fillable = [
        'nama_bahan',
        'satuan',
        'stok',
        'stok_minimum',
    ];

    public function detailProduksi()
    {
        return $this->hasMany(ProduksiBahanBaku::class, 'bahan_baku_id');
    }
}
