<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';

    protected $fillable = [
        'kategori_id',
        'kode_produk',
        'nama_produk',
        'deskripsi',
        'harga_jual',
        'harga_modal',
        'stok',
        'stok_besar',
        'stok_kecil',
        'stok_minimum',
        'foto',
        'status_aktif',
        'status_ketersediaan',
        'is_khusus_bundling',
        'punya_ukuran',
        'harga_besar',
        'harga_kecil',
    ];

    protected $casts = [
        'punya_ukuran' => 'boolean',
        'is_khusus_bundling' => 'boolean',
        'harga_besar' => 'decimal:2',
        'harga_kecil' => 'decimal:2',
        'stok_besar' => 'integer',
        'stok_kecil' => 'integer',
    ];

    protected $appends = [
        'promo_aktif',
        'promos_aktif_semua',
        'harga_diskon'
    ];

    public function scopeReguler($query)
    {
        return $query->where('is_khusus_bundling', false);
    }

    public function scopeKhususBundling($query)
    {
        return $query->where('is_khusus_bundling', true);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

    public function produksi()
    {
        return $this->hasMany(Produksi::class);
    }

    public function promos()
    {
        return $this->belongsToMany(Promo::class, 'promo_produk', 'produk_id', 'promo_id')->withTimestamps();
    }

    public function getPromosAktifSemuaAttribute()
    {
        $now = now();
        $isPromoBerlaku = function ($promo) use ($now) {
            if (!$promo->status_aktif) return false;
            if ($promo->mulai_berlaku > $now) return false;
            if ($promo->selesai_berlaku && $promo->selesai_berlaku < $now) return false;
            return true;
        };

        if ($this->relationLoaded('promos')) {
            return $this->promos->filter($isPromoBerlaku)->values();
        }

        return $this->promos()
            ->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            })
            ->get();
    }

    public function getPromoAktifAttribute()
    {
        $now = now();
        $isPromoBerlaku = function ($promo) use ($now) {
            if (!$promo->status_aktif) return false;
            if ($promo->mulai_berlaku > $now) return false;
            if ($promo->selesai_berlaku && $promo->selesai_berlaku < $now) return false;
            return true;
        };

        if ($this->relationLoaded('promos')) {
            return $this->promos->filter($isPromoBerlaku)->sortByDesc(function ($p) {
                return $p->jenis_potongan === 'nominal'
                    ? $p->nominal_potongan
                    : (($p->persentase ?? 0) / 100 * $this->getHarga());
            })->first();
        }

        return $this->promos()
            ->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            })
            ->get()
            ->sortByDesc(function ($p) {
                return $p->jenis_potongan === 'nominal'
                    ? $p->nominal_potongan
                    : (($p->persentase ?? 0) / 100 * $this->getHarga());
            })
            ->first();
    }

    public function getHargaDiskonAttribute()
    {
        return $this->hargaSetelahPromo();
    }

    public function hargaSetelahPromo(?string $ukuran = null): float
    {
        $hargaDasar = $this->getHarga($ukuran);
        $promo = $this->promo_aktif;
        if ($promo) {
            if ($promo->jenis_potongan === 'nominal') {
                $diskon = $promo->nominal_potongan;
            } else {
                $diskon = (($promo->persentase ?? 0) / 100) * $hargaDasar;
            }
            return max(0, $hargaDasar - $diskon);
        }
        return $hargaDasar;
    }

    public function promoProdukEvents()
    {
        return $this->hasMany(PromoProdukEvent::class);
    }

    public function getHarga(?string $ukuran = null): float
    {
        if ($this->punya_ukuran) {
            if ($ukuran === 'Besar') {
                return (float) ($this->harga_besar ?? 0);
            }
            if ($ukuran === 'Kecil') {
                return (float) ($this->harga_kecil ?? 0);
            }
            return (float) ($this->harga_kecil ?? $this->harga_besar ?? $this->harga_jual ?? 0);
        }

        return (float) ($this->harga_jual ?? 0);
    }

    public function stokUntukUkuran(?string $ukuran = null): int
    {
        if ($this->punya_ukuran) {
            if ($ukuran === 'Besar') {
                return (int) ($this->stok_besar ?? 0);
            }
            if ($ukuran === 'Kecil') {
                return (int) ($this->stok_kecil ?? 0);
            }
            return (int) ($this->stok_besar ?? 0) + (int) ($this->stok_kecil ?? 0);
        }

        return (int) $this->stok;
    }

    public function habisUntukUkuran(?string $ukuran = null): bool
    {
        return $this->status_ketersediaan === 'perlu_diproduksi' || $this->stokUntukUkuran($ukuran) <= 0;
    }

    public function habisSemuaUkuran(): bool
    {
        if (!$this->punya_ukuran) {
            return $this->habisUntukUkuran();
        }

        if ($this->status_ketersediaan === 'perlu_diproduksi') {
            return true;
        }

        return (int) ($this->stok_besar ?? 0) <= 0 && (int) ($this->stok_kecil ?? 0) <= 0;
    }

    public function decrementStokUkuran(?string $ukuran, int $jumlah): void
    {
        if ($jumlah <= 0) {
            return;
        }

        if ($this->punya_ukuran && $ukuran === 'Besar') {
            $this->decrement('stok_besar', $jumlah);
            $this->decrement('stok', $jumlah);
            return;
        }

        if ($this->punya_ukuran && $ukuran === 'Kecil') {
            $this->decrement('stok_kecil', $jumlah);
            $this->decrement('stok', $jumlah);
            return;
        }

        $this->decrement('stok', $jumlah);
    }

    public function incrementStokUkuran(?string $ukuran, int $jumlah): void
    {
        if ($jumlah <= 0) {
            return;
        }

        if ($this->punya_ukuran && $ukuran === 'Besar') {
            $this->increment('stok_besar', $jumlah);
            $this->increment('stok', $jumlah);
            return;
        }

        if ($this->punya_ukuran && $ukuran === 'Kecil') {
            $this->increment('stok_kecil', $jumlah);
            $this->increment('stok', $jumlah);
            return;
        }

        $this->increment('stok', $jumlah);
    }
}