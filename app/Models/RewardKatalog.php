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
        'target_member',
        'batas_penggunaan_per_member',
        'tanggal_mulai',
        'tanggal_selesai',
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
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'batas_penggunaan_per_member' => 'integer',
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

    public function members()
    {
        return $this->belongsToMany(Member::class, 'voucher_members', 'reward_katalog_id', 'member_id')->withTimestamps();
    }

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class, 'reward_katalog_id');
    }

    public function penukaran()
    {
        return $this->hasMany(PenukaranPoin::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function scopeBerlakuTanggal($query, $date = null)
    {
        $d = $date ? \Carbon\Carbon::parse($date)->toDateString() : now()->toDateString();
        return $query->where(function ($q) use ($d) {
            $q->whereNull('tanggal_mulai')->orWhereDate('tanggal_mulai', '<=', $d);
        })->where(function ($q) use ($d) {
            $q->whereNull('tanggal_selesai')->orWhereDate('tanggal_selesai', '>=', $d);
        });
    }

    public function statusKedaluwarsa(): bool
    {
        $today = now()->toDateString();
        if ($this->tanggal_selesai && $this->tanggal_selesai->toDateString() < $today) {
            return true;
        }
        return false;
    }

    public function statusBelumMulai(): bool
    {
        $today = now()->toDateString();
        if ($this->tanggal_mulai && $this->tanggal_mulai->toDateString() > $today) {
            return true;
        }
        return false;
    }

    public function jumlahPemakaianMember(int $memberId): int
    {
        return $this->usages()->where('member_id', $memberId)->count();
    }

    public function batasPemakaianTercapai(int $memberId): bool
    {
        $batas = $this->batas_penggunaan_per_member ?: 1;
        return $this->jumlahPemakaianMember($memberId) >= $batas;
    }

    public function berhakDigunakan(Member $member): bool
    {
        if (!$this->status_aktif) {
            return false;
        }

        if ($this->target_member === 'khusus') {
            $terdaftar = $this->members()->where('members.id', $member->id)->exists();
            if (!$terdaftar) {
                return false;
            }
        }

        if ($this->batasPemakaianTercapai($member->id)) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan status ketersediaan voucher untuk member beserta alasannya jika tidak aktif
     */
    public function cekStatusUntukMember(Member $member): array
    {
        if (!$this->status_aktif) {
            return ['tersedia' => false, 'alasan' => 'Voucher tidak aktif'];
        }

        if ($this->target_member === 'khusus') {
            $terdaftar = $this->members()->where('members.id', $member->id)->exists();
            if (!$terdaftar) {
                return ['tersedia' => false, 'alasan' => 'Khusus member tertentu'];
            }
        }

        if ($this->batasPemakaianTercapai($member->id)) {
            $batas = $this->batas_penggunaan_per_member ?: 1;
            return ['tersedia' => false, 'alasan' => "Batas penggunaan tercapai ($batas kali)"];
        }

        return ['tersedia' => true, 'alasan' => null];
    }
}

