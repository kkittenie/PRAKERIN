<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VarianRasa extends Model
{
    protected $table = 'varian_rasas';

    protected $fillable = [
        'nama_rasa',
        'gambar',
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
        return $query->orderBy('nama_rasa');
    }

    /**
     * Baris stok rasa ini di produk-produk. Dipakai untuk ngecek "masih dipakai
     * produk atau enggak" sebelum rasa boleh dihapus (FK-nya cascade, jadi kalau
     * dihapus begitu saja, stok rasa di semua produk ikut hilang).
     */
    public function produkVarianRasas()
    {
        return $this->hasMany(ProdukVarianRasa::class, 'varian_rasa_id');
    }
}