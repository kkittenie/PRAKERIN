<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Member extends Authenticatable
{
    use Notifiable, LogsActivity;

    protected $fillable = ['nama', 'username', 'no_hp', 'email', 'foto', 'poin', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['level', 'warna_badge_level'];

    public static function normalizeNoHp(string $noHp): string
    {
        $noHp = preg_replace('/[^0-9+]/', '', trim($noHp));

        if (str_starts_with($noHp, '0')) {
            return '+62' . substr($noHp, 1);
        }

        if (!str_starts_with($noHp, '+')) {
            return '+62' . $noHp;
        }

        return $noHp;
    }

    public static function toLocalNoHp(string $normalizedNoHp): string
    {
        if (str_starts_with($normalizedNoHp, '+62')) {
            return '0' . substr($normalizedNoHp, 3);
        }

        return $normalizedNoHp;
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getKelengkapanProfilAttribute(): int
    {
        $fields = [$this->email];
        $terisi = count(array_filter($fields, fn($f) => !is_null($f) && $f !== ''));

        return (int) round((1 + $terisi) / 2 * 100);
    }

    protected function levelAktif(): ?MemberLevel
    {
        return MemberLevel::aktif()
            ->where('minimal_poin', '<=', $this->poin)
            ->reorder('minimal_poin', 'desc')
            ->first();
    }

    public function getLevelAttribute(): string
    {
        $level = $this->levelAktif();

        if ($level) {
            return $level->nama_level;
        }

        $levelTerendah = MemberLevel::aktif()->orderBy('minimal_poin')->first();

        return $levelTerendah->nama_level ?? 'Member';
    }

    public function getWarnaBadgeLevelAttribute(): string
    {
        $level = $this->levelAktif();

        if ($level) {
            return $level->warna_badge ?? 'gray';
        }

        $levelTerendah = MemberLevel::aktif()->orderBy('minimal_poin')->first();

        return $levelTerendah->warna_badge ?? 'gray';
    }

    public function getProgresTierAttribute(): ?array
    {
        $levelSaatIni = MemberLevel::aktif()
            ->where('minimal_poin', '<=', $this->poin)
            ->reorder('minimal_poin', 'desc')
            ->first();

        $levelBerikutnya = MemberLevel::aktif()
            ->where('minimal_poin', '>', $this->poin)
            ->orderBy('minimal_poin')
            ->first();

        if (!$levelBerikutnya) {
            return null;
        }

        $batasSaatIni = $levelSaatIni->minimal_poin ?? 0;
        $batasBerikutnya = $levelBerikutnya->minimal_poin;

        $persen = (($this->poin - $batasSaatIni) / max(1, $batasBerikutnya - $batasSaatIni)) * 100;

        return [
            'tier_berikutnya' => $levelBerikutnya->nama_level,
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

    public function stamps()
    {
        return $this->hasMany(MemberStamp::class);
    }

    public function stampRedemptions()
    {
        return $this->hasMany(StampRedemption::class);
    }

    protected static function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama', 'username', 'no_hp', 'poin'])
            ->logOnlyDirty()
            ->useLogName('member');
    }
}