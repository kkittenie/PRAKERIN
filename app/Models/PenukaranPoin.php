<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PenukaranPoin extends Model
{
    protected $table = 'penukaran_poin';

    protected $fillable = [
        'member_id',
        'reward_katalog_id',
        'kode_redeem',
        'poin_terpakai',
        'status',
        'berlaku_sampai',
        'diambil_pada',
        'transaksi_id',
    ];

    protected $casts = [
        'diambil_pada' => 'datetime',
        'berlaku_sampai' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (PenukaranPoin $penukaran) {
            $penukaran->kode_redeem ??= self::buatKodeUnik();
        });
    }

    public static function buatKodeUnik(): string
    {
        do {
            $kode = strtoupper(Str::random(3)).'-'.strtoupper(Str::random(4));
        } while (self::where('kode_redeem', $kode)->exists());

        return $kode;
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function reward()
    {
        return $this->belongsTo(RewardKatalog::class, 'reward_katalog_id');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    public function scopeAktifDigunakan($query)
    {
        return $query->where('status', 'menunggu_diambil')
            ->where(function ($q) {
                $q->whereNull('berlaku_sampai')->orWhereDate('berlaku_sampai', '>=', now()->toDateString());
            });
    }

    public function sudahExpired(): bool
    {
        if (! $this->berlaku_sampai) {
            return false;
        }

        return $this->berlaku_sampai->endOfDay()->isPast();
    }
}
