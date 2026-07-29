<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produksi extends Model
{
    protected $table = 'produksis';

    protected $fillable = [
        'tanggal',
        'jam_produksi',
        'produk_id',
        'jumlah_hasil',
        'user_id',
        'keterangan',
        'kadaluarsa_tipe',
        'kadaluarsa_tanggal',
        'kadaluarsa_jam',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailBahan()
    {
        return $this->hasMany(ProduksiBahanBaku::class, 'produksi_id');
    }
}
