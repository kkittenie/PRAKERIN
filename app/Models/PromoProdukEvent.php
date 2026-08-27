<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoProdukEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'nama_event',
        'isi_normal',
        'isi_event',
        'harga_event',
        'mulai_berlaku',
        'selesai_berlaku',
        'status_aktif',
    ];

    protected $casts = [
        'isi_normal' => 'integer',
        'isi_event' => 'integer',
        'harga_event' => 'decimal:2',
        'mulai_berlaku' => 'datetime',
        'selesai_berlaku' => 'datetime',
        'status_aktif' => 'boolean',
    ];

    protected $appends = [
        'stok_tersedia',
        'perlu_diproduksi',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function ukurans()
    {
        return $this->belongsToMany(Ukuran::class, 'promo_produk_event_ukurans', 'promo_produk_event_id', 'ukuran_id')->withTimestamps();
    }

    /**
     * ID ukuran yang dibatasi buat event ini. Kosong = gak dibatasi
     * (berlaku ke semua ukuran produk ini) — sama kayak sebelum fitur ini ada.
     */
    public function ukuranIdsTerbatas(): \Illuminate\Support\Collection
    {
        return $this->relationLoaded('ukurans')
            ? $this->ukurans->pluck('id')
            : $this->ukurans()->pluck('ukurans.id');
    }

    public function berlakuUntukUkuran(?int $ukuranId): bool
    {
        $daftar = $this->ukuranIdsTerbatas();
        if ($daftar->isEmpty()) {
            return true;
        }

        return $ukuranId !== null && $daftar->contains($ukuranId);
    }

    public function getSedangBerlangsungAttribute(): bool
    {
        $sekarang = now();

        return $this->status_aktif
            && $sekarang->between($this->mulai_berlaku, $this->selesai_berlaku);
    }

        public function scopeBerlaku($query)
    {
        $now = now();

        return $query->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where('selesai_berlaku', '>=', $now);
    }

    public function getStokTersediaAttribute(): int
    {
        if (!$this->produk || $this->isi_event <= 0) {
            return 0;
        }

        $stokDasar = (int) $this->produk->stok;

        $ukuranIds = $this->ukuranIdsTerbatas();
        if ($this->produk->punya_ukuran && $ukuranIds->isNotEmpty()) {
            // Event dibatasi ke ukuran tertentu — pakai stok ukuran itu
            // aja, jangan digabung sama ukuran lain yang gak ikut event.
            $stokDasar = (int) $this->produk->produkUkurans()
                ->whereIn('ukuran_id', $ukuranIds)
                ->sum('stok');
        }

        return intdiv($stokDasar, (int) $this->isi_event);
    }

    public function getPerluDiproduksiAttribute(): bool
    {
        return $this->stok_tersedia <= 0;
    }
}