<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardKatalog extends Model
{
    protected $table = 'reward_katalog';

    protected $fillable = [
        'kode_reward',
        'nama',
        'jenis',
        'deskripsi',
        'gambar',
        'poin_dibutuhkan',
        'stok',
        'status_aktif',
        'jenis_potongan',
        'nominal_potongan',
        'persentase',
        'maksimal_potongan',
        'min_pembelian',
        'masa_berlaku_hari',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'nominal_potongan' => 'decimal:2',
        'persentase' => 'decimal:2',
        'maksimal_potongan' => 'decimal:2',
        'min_pembelian' => 'decimal:2',
    ];

    public function getKodeRewardAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        return 'RWD' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'reward_katalog_produk', 'reward_katalog_id', 'produk_id')->withTimestamps();
    }

    public function penukaran()
    {
        return $this->hasMany(PenukaranPoin::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true)
            ->where(function ($q) {
                $q->whereNull('stok')->orWhere('stok', '>', 0);
            });
    }

    public function tersediaUntuk(Member $member): bool
    {
        if (! $this->status_aktif) {
            return false;
        }

        if (! is_null($this->stok) && $this->stok <= 0) {
            return false;
        }

        return $member->poin >= $this->poin_dibutuhkan;
    }
}
