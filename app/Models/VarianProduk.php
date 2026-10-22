<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Varian milik produk "Stok Bersama" (global), mis. topping donat.
 * Stok tidak disimpan di sini (ikut detail produk utama, dipakai bersama). Harga jual & modal
 * boleh berbeda per varian; kalau kosong, memakai harga detail produk utama.
 */
class VarianProduk extends Model
{
    protected $table = 'varian_produks';

    protected $fillable = [
        'produk_id',
        'kode_produk',
        'ukuran',
        'variasi',
        'foto',
        'harga_modal',
        'harga_jual',
        'status_aktif',
        'urutan',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'urutan' => 'integer',
        'harga_modal' => 'float',
        'harga_jual' => 'float',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    /** Harga jual varian ini; kalau belum diisi memakai harga detail produk utama. */
    public function hargaJualEfektif(?DetailProduk $utama = null): float
    {
        if ($this->harga_jual !== null) {
            return (float) $this->harga_jual;
        }

        $utama ??= $this->produk?->daftarDetail()->first();

        return (float) ($utama?->harga_jual ?? 0);
    }

    public function hargaModalEfektif(?DetailProduk $utama = null): float
    {
        if ($this->harga_modal !== null) {
            return (float) $this->harga_modal;
        }

        $utama ??= $this->produk?->daftarDetail()->first();

        return (float) ($utama?->harga_modal ?? 0);
    }

    public function getNamaLengkapAttribute(): string
    {
        return collect([$this->ukuran, $this->variasi])->filter()->implode(' / ');
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return null;
        }

        return (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://'))
            ? $this->foto
            : asset('storage/' . $this->foto);
    }

    protected static function booted()
    {
        // Kode dikosongkan -> dibuatkan otomatis: PRD{id produk}-V{nomor}.
        static::creating(function (VarianProduk $varian) {
            if (empty($varian->kode_produk)) {
                $varian->kode_produk = static::buatKode((int) $varian->produk_id);
            }
        });
    }

    public static function buatKode(int $produkId): string
    {
        $induk = 'PRD' . str_pad((string) $produkId, 4, '0', STR_PAD_LEFT) . '-V';
        $nomor = static::where('produk_id', $produkId)->count() + 1;

        do {
            $kandidat = $induk . str_pad((string) $nomor, 2, '0', STR_PAD_LEFT);
            $nomor++;
        } while (
            static::where('kode_produk', $kandidat)->exists()
            || DetailProduk::where('kode_produk', $kandidat)->exists()
        );

        return $kandidat;
    }
}