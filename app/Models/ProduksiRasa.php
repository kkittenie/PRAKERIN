<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiRasa extends Model
{
    protected $table = 'produksi_rasas';

    protected $fillable = [
        'produksi_id',
        'produk_ukuran_id',
        'varian_rasa_id',
        'nama_rasa',
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

    public function varianRasa()
    {
        return $this->belongsTo(VarianRasa::class, 'varian_rasa_id');
    }
}