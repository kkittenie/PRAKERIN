<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StampProgramHadiah extends Model
{
    protected $table = 'stamp_program_hadiah';

    protected $fillable = ['stamp_program_id', 'produk_id', 'detail_produk_id', 'varian_produk_id', 'jumlah'];

    public function stampProgram()
    {
        return $this->belongsTo(StampProgram::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function detailProduk()
    {
        return $this->belongsTo(DetailProduk::class, 'detail_produk_id');
    }

    public function varianProduk()
    {
        return $this->belongsTo(VarianProduk::class, 'varian_produk_id');
    }

    /** Nama produk + varian/ukuran, mis. "Donat Coklat (Besar / Coklat)". */
    public function getNamaTampilAttribute(): string
    {
        $nama = $this->produk->nama_produk ?? 'Produk sudah dihapus';
        $varian = $this->varianProduk?->nama_lengkap ?: $this->detailProduk?->nama_lengkap;

        return $varian ? "{$nama} ({$varian})" : $nama;
    }

    /** Stok yang relevan: stok varian kalau dipilih, kalau tidak stok total produk. */
    public function getStokTersediaAttribute(): ?int
    {
        if ($this->varian_produk_id) {
            return $this->produk ? (int) $this->produk->stok : null;
        }

        if ($this->detail_produk_id) {
            return $this->detailProduk ? (int) $this->detailProduk->stok : null;
        }

        return $this->produk ? (int) $this->produk->stok : null;
    }
}