<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ukuran extends Model
{
    protected $table = 'ukurans';

    protected $fillable = [
        'nama_ukuran',
        'urutan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('nama_ukuran');
    }

    /**
     * Baris produk_ukurans yang pakai ukuran ini. Dipakai buat ngecek
     * "masih dipakai produk atau enggak" sebelum boleh dihapus — sama
     * kayak Satuan::bahanBakus() / KategoriProduk::produk().
     */
    public function produkUkurans()
    {
        return $this->hasMany(ProdukUkuran::class, 'ukuran_id');
    }
}