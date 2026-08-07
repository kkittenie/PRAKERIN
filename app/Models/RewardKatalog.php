<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardKatalog extends Model
{
    protected $table = 'reward_katalog';

    protected $fillable = [
        'nama',
        'jenis',
        'deskripsi',
        'gambar',
        'poin_dibutuhkan',
        'stok',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

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
