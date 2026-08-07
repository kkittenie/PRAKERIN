<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Member extends Authenticatable
{
    use Notifiable, LogsActivity;

    protected $fillable = ['nama', 'no_hp', 'poin', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['level'];

    // Ambang batas poin per tier. Samakan dengan aturan yang dipakai
    // di layar Pengaturan Member kalau nanti dibuat configurable.
    public const TIER_THRESHOLDS = [
        'Bronze' => 0,
        'Silver' => 300,
        'Gold' => 500,
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getLevelAttribute(): string
    {
        if ($this->poin >= self::TIER_THRESHOLDS['Gold']) {
            return 'Gold';
        }

        if ($this->poin >= self::TIER_THRESHOLDS['Silver']) {
            return 'Silver';
        }

        return 'Bronze';
    }

    /**
     * Info progress menuju tier berikutnya, dipakai untuk progress bar
     * di kartu member. Null kalau sudah di tier tertinggi.
     */
    public function getProgresTierAttribute(): ?array
    {
        $tierBerikutnya = match ($this->level) {
            'Bronze' => 'Silver',
            'Silver' => 'Gold',
            default => null,
        };

        if (! $tierBerikutnya) {
            return null;
        }

        $batasSaatIni = self::TIER_THRESHOLDS[$this->level];
        $batasBerikutnya = self::TIER_THRESHOLDS[$tierBerikutnya];

        $persen = (($this->poin - $batasSaatIni) / max(1, $batasBerikutnya - $batasSaatIni)) * 100;

        return [
            'tier_berikutnya' => $tierBerikutnya,
            'poin_dibutuhkan' => max(0, $batasBerikutnya - $this->poin),
            'persen' => (int) min(100, max(0, round($persen))),
        ];
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function penukaranPoin()
    {
        return $this->hasMany(PenukaranPoin::class);
    }

    protected static function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama', 'no_hp', 'poin'])
            ->logOnlyDirty()
            ->useLogName('member');
    }
}
