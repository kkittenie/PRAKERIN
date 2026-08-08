<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Member extends Authenticatable
{
    use Notifiable, LogsActivity;

    protected $fillable = ['nama', 'no_hp', 'email', 'tanggal_lahir', 'gender', 'foto', 'poin', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['level'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
        ];
    }

    public function getKelengkapanProfilAttribute(): int
    {
        $fields = [$this->email, $this->tanggal_lahir, $this->gender];
        $terisi = count(array_filter($fields, fn ($f) => ! is_null($f) && $f !== ''));

        return (int) round((1 + $terisi) / 4 * 100); // username selalu terisi + 3 field opsional
    }

    public function getLevelAttribute(): string
    {
        $level = MemberLevel::aktif()
            ->where('minimal_poin', '<=', $this->poin)
            ->orderByDesc('minimal_poin')
            ->first();

        if ($level) {
            return $level->nama_level;
        }

        // Belum ada level aktif yang jangkauan poinnya mencakup member ini
        // (mis. level terendah belum dibuat, atau minimal_poin-nya di atas 0).
        // Fallback ke level aktif dengan minimal_poin terendah, bukan nama tetap.
        $levelTerendah = MemberLevel::aktif()->orderBy('minimal_poin')->first();

        return $levelTerendah->nama_level ?? 'Member';
    }

    public function getProgresTierAttribute(): ?array
    {
        $levelSaatIni = MemberLevel::aktif()
            ->where('minimal_poin', '<=', $this->poin)
            ->orderByDesc('minimal_poin')
            ->first();

        $levelBerikutnya = MemberLevel::aktif()
            ->where('minimal_poin', '>', $this->poin)
            ->orderBy('minimal_poin')
            ->first();

        if (! $levelBerikutnya) {
            return null; // sudah di level tertinggi
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
            ->logOnly(['nama', 'no_hp', 'poin'])
            ->logOnlyDirty()
            ->useLogName('member');
    }
}