<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiUkuran extends Model
{
    protected $table = 'produksi_ukurans';

    protected $fillable = [
        'produksi_id',
        'produk_ukuran_id',
        'jumlah_hasil',
        'kebutuhan',
    ];

    protected $casts = [
        'jumlah_hasil' => 'integer',
        'kebutuhan' => 'integer',
    ];

    public function produksi()
    {
        return $this->belongsTo(Produksi::class, 'produksi_id');
    }

    public function produkUkuran()
    {
        return $this->belongsTo(ProdukUkuran::class, 'produk_ukuran_id')->with('ukuran');
    }
}