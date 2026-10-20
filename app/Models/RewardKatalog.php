<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardKatalog extends Model
{
    protected $table = 'reward_katalog';

    public const TARGET_SEMUA = 'semua';
    public const TARGET_KHUSUS = 'khusus';
    public const TARGET_NON_MEMBER = 'non_member';

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
        return $this->belongsToMany(Produk::class, 'reward_katalog_produk', 'reward_katalog_id', 'produk_id')->withTimestamps()->distinct();
    }

    public function rewardProduks()
    {
        return $this->hasMany(RewardKatalogProduk::class, 'reward_katalog_id');
    }

    public function detailIdsUntukProduk(int $produkId): \Illuminate\Support\Collection
    {
        $rp = $this->relationLoaded('rewardProduks') ? $this->rewardProduks : $this->rewardProduks()->get();
        return $rp
            ->where('produk_id', $produkId)
            ->pluck('detail_produk_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    public function varianIdsUntukProduk(int $produkId): \Illuminate\Support\Collection
    {
        $rp = $this->relationLoaded('rewardProduks') ? $this->rewardProduks : $this->rewardProduks()->get();
        return $rp
            ->where('produk_id', $produkId)
            ->pluck('varian_produk_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    public function berlakuUntukItem(int $produkId, ?int $detailProdukId = null, ?int $varianProdukId = null): bool
    {
        $rp = $this->relationLoaded('rewardProduks') ? $this->rewardProduks : $this->rewardProduks()->get();
        if ($rp->isEmpty()) {
            return true;
        }

        $barisProduk = $rp->where('produk_id', $produkId);
        if ($barisProduk->isEmpty()) {
            return false;
        }

        $detailIds = $barisProduk->pluck('detail_produk_id')->filter()->map(fn ($id) => (int) $id);
        $varianIds = $barisProduk->pluck('varian_produk_id')->filter()->map(fn ($id) => (int) $id);

        if ($detailIds->isEmpty() && $varianIds->isEmpty()) {
            return true;
        }

        if ($varianProdukId !== null && $varianIds->contains((int) $varianProdukId)) {
            return true;
        }

        if ($detailProdukId !== null && $detailIds->contains((int) $detailProdukId)) {
            return true;
        }

        return false;
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

    /**
     * Voucher ini khusus untuk pembeli non member (dipakai di kasir tanpa memilih member).
     */
    public function untukNonMember(): bool
    {
        return $this->target_member === self::TARGET_NON_MEMBER;
    }

    public function labelTarget(): string
    {
        return match ($this->target_member) {
            self::TARGET_KHUSUS => 'Member Tertentu',
            self::TARGET_NON_MEMBER => 'Non Member',
            default => 'Semua Member',
        };
    }

    /**
     * Pembeli non member tidak terdata, jadi pemakaiannya dihitung total
     * (baris voucher_usages dengan member_id NULL).
     */
    public function jumlahPemakaianNonMember(): int
    {
        return $this->usages()->whereNull('member_id')->count();
    }

    public function kuotaNonMember(): int
    {
        return (int) ($this->batas_penggunaan_per_member ?: 1);
    }

    public function sisaKuotaNonMember(): int
    {
        return max(0, $this->kuotaNonMember() - $this->jumlahPemakaianNonMember());
    }

    public function berhakDigunakanNonMember(): bool
    {
        return $this->status_aktif
            && $this->untukNonMember()
            && $this->sisaKuotaNonMember() > 0;
    }

    public function berhakDigunakan(Member $member): bool
    {
        if (!$this->status_aktif) {
            return false;
        }

        // Voucher non member tidak berlaku untuk member.
        if ($this->untukNonMember()) {
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

        if ($this->untukNonMember()) {
            return ['tersedia' => false, 'alasan' => 'Khusus pembeli non member'];
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