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

        return intdiv((int) $this->produk->stok, (int) $this->isi_event);
    }

    public function getPerluDiproduksiAttribute(): bool
    {
        return $this->stok_tersedia <= 0;
    }
}