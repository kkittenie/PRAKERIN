<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Member extends Authenticatable
{
    use Notifiable, LogsActivity;

    protected $fillable = ['kode_member', 'nama', 'username', 'no_hp', 'email', 'foto', 'poin', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['level', 'warna_badge_level'];

    /**
     * Generate kode member unik berformat MBR000001, MBR000002, dst,
     * berdasarkan id member yang sudah tersimpan di DB (jadi dijamin unik
     * karena mengikuti auto increment, tidak ada resiko race condition).
     */
    public static function generateKodeMember(int $id): string
    {
        return 'MBR' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

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

        // Kalau sampai sini, artinya nggak ada tier aktif yang minimal_poin-nya
        // <= poin member (misal tier 0-poin/Bronze kebetulan lagi nonaktif).
        // JANGAN jatuh ke tier aktif manapun yang minimal_poin-nya > 0 --
        // dulu di sini dipilih "tier aktif paling rendah" tanpa ngecek dulu
        // apa member beneran udah cukup poin buat itu, jadi member baru
        // dengan 0 poin bisa aja ketampil di tier tertinggi kalau cuma itu
        // yang aktif. Fallback yang aman: cuma pakai tier aktif itu kalau
        // ambang batasnya beneran 0 (jadi semua orang emang berhak dapet).
        $levelTerendah = MemberLevel::aktif()->orderBy('minimal_poin')->first();

        if ($levelTerendah && (int) $levelTerendah->minimal_poin === 0) {
            return $levelTerendah->nama_level;
        }

        return 'Member';
    }

    public function getWarnaBadgeLevelAttribute(): string
    {
        $level = $this->levelAktif();

        if ($level) {
            return $level->warna_badge ?? 'gray';
        }

        // Lihat penjelasan di getLevelAttribute() -- fallback yang sama:
        // cuma pakai tier aktif paling rendah kalau ambang batasnya 0.
        $levelTerendah = MemberLevel::aktif()->orderBy('minimal_poin')->first();

        if ($levelTerendah && (int) $levelTerendah->minimal_poin === 0) {
            return $levelTerendah->warna_badge ?? 'gray';
        }

        return 'gray';
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