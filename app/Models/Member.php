<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Member extends Authenticatable
{
    use Notifiable, LogsActivity;

    protected $fillable = ['kode_member', 'nama', 'username', 'no_hp', 'email', 'foto', 'poin', 'password', 'google_id'];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['level', 'warna_badge_level'];

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

    public function getTotalTransaksiAttribute(): float
    {
        return (float) $this->transaksi()
            ->where('status_pembayaran', 'Lunas')
            ->sum('total_bayar');
    }

    protected function levelAktif(): ?MemberLevel
    {
        $total = $this->total_transaksi;

        return MemberLevel::aktif()
            ->where('minimal_transaksi', '<=', $total)
            ->reorder('minimal_transaksi', 'desc')
            ->first();
    }

    public function getLevelAttribute(): string
    {
        $level = $this->levelAktif();

        if ($level) {
            return $level->nama_level;
        }

        $levelTerendah = MemberLevel::aktif()->orderBy('minimal_transaksi')->first();

        if ($levelTerendah && (float) $levelTerendah->minimal_transaksi <= 0) {
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

        $levelTerendah = MemberLevel::aktif()->orderBy('minimal_transaksi')->first();

        if ($levelTerendah && (float) $levelTerendah->minimal_transaksi <= 0) {
            return $levelTerendah->warna_badge ?? 'gray';
        }

        return 'gray';
    }

    public function getProgresTierAttribute(): ?array
    {
        $total = $this->total_transaksi;

        $levelSaatIni = MemberLevel::aktif()
            ->where('minimal_transaksi', '<=', $total)
            ->reorder('minimal_transaksi', 'desc')
            ->first();

        $levelBerikutnya = MemberLevel::aktif()
            ->where('minimal_transaksi', '>', $total)
            ->orderBy('minimal_transaksi')
            ->first();

        if (!$levelBerikutnya) {
            return null;
        }

        $batasSaatIni = (float) ($levelSaatIni->minimal_transaksi ?? 0);
        $batasBerikutnya = (float) $levelBerikutnya->minimal_transaksi;

        $persen = (($total - $batasSaatIni) / max(1, $batasBerikutnya - $batasSaatIni)) * 100;

        return [
            'tier_berikutnya' => $levelBerikutnya->nama_level,
            'transaksi_dibutuhkan' => max(0, $batasBerikutnya - $total),
            'poin_dibutuhkan' => max(0, $batasBerikutnya - $total),
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

    public function vouchers()
    {
        return $this->belongsToMany(RewardKatalog::class, 'voucher_members', 'member_id', 'reward_katalog_id')->withTimestamps();
    }

    public function voucherUsages()
    {
        return $this->hasMany(VoucherUsage::class);
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