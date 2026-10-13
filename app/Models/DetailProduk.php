<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Detail produk = satu baris kombinasi ukuran + variasi dari sebuah produk,
 * lengkap dengan kode, harga, stok, dan status aktif.
 * Kode, harga, dan stok disimpan di sini, bukan di tabel produks.
 *
 * (Dulu bernama ProdukVarian / tabel produk_varians.)
 */
class DetailProduk extends Model
{
    protected $table = 'detail_produks';

    protected $fillable = [
        'produk_id',
        'ukuran',
        'variasi',
        'kode_barcode',
        'harga_modal',
        'harga_jual',
        'stok',
        'stok_minimum',
        'foto',
        'status_ketersediaan',
        'status_aktif',
        'urutan',
        'ukuran_id',
        'varian_rasa_id',
    ];

    protected $casts = [
        'harga_modal' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'status_aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    public function getPerluDiproduksiAttribute(): bool
    {
        return $this->status_ketersediaan === 'perlu_diproduksi' || $this->stok <= 0;
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
        // Status ketersediaan selalu mengikuti stok: > 0 = stok ada, selain itu = perlu diproduksi.
        static::saving(function (DetailProduk $detail) {
            $detail->status_ketersediaan = (int) $detail->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
        });

        // Kalau kode dikosongkan, dibuatkan otomatis dari kode produk.
        static::creating(function (DetailProduk $detail) {
            if (empty($detail->kode_barcode)) {
                $detail->kode_barcode = static::buatKodeBarcode((int) $detail->produk_id);
            }
        });
    }

    public static function buatKodeBarcode(int $produkId): ?string
    {
        $kodeProduk = Produk::whereKey($produkId)->value('kode_produk');
        if (!$kodeProduk) {
            return null;
        }

        $nomor = static::where('produk_id', $produkId)->count() + 1;

        do {
            $kandidat = $kodeProduk . '-' . str_pad($nomor, 2, '0', STR_PAD_LEFT);
            $nomor++;
        } while (static::where('kode_barcode', $kandidat)->exists());

        return $kandidat;
    }
}