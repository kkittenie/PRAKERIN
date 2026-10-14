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
        'kode_produk',
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

        // Kalau kode dikosongkan, dibuatkan otomatis: PRD{id produk}-{nomor urut}.
        static::creating(function (DetailProduk $detail) {
            if (empty($detail->kode_produk)) {
                $detail->kode_produk = static::buatKodeProduk((int) $detail->produk_id);
            }
        });
    }

    /** Nama lama (kode_barcode) tetap bisa dibaca/ditulis supaya view dan JS yang belum diganti tidak error. */
    public function getKodeBarcodeAttribute(): ?string
    {
        return $this->kode_produk;
    }

    public function setKodeBarcodeAttribute($value): void
    {
        $this->attributes['kode_produk'] = $value;
    }

    public static function buatKodeProduk(int $produkId): string
    {
        $induk = 'PRD' . str_pad((string) $produkId, 4, '0', STR_PAD_LEFT);
        $nomor = static::where('produk_id', $produkId)->count() + 1;

        do {
            $kandidat = $induk . '-' . str_pad((string) $nomor, 2, '0', STR_PAD_LEFT);
            $nomor++;
        } while (static::where('kode_produk', $kandidat)->exists());

        return $kandidat;
    }
}