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
        'diambil_pada',
    ];

    protected $casts = [
        'diambil_pada' => 'datetime',
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
}
