<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class StampProgram extends Model
{
    use LogsActivity;

    protected $table = 'stamp_programs';

    protected $fillable = [
        'nama_program', 'deskripsi', 'minimal_pembelian', 'jumlah_stamp_dibutuhkan',
        'maksimal_redeem', 'jumlah_redeem', 'tanggal_mulai', 'tanggal_berakhir', 'status_aktif',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
        'status_aktif' => 'boolean',
        'minimal_pembelian' => 'integer',
        'jumlah_stamp_dibutuhkan' => 'integer',
        'maksimal_redeem' => 'integer',
        'jumlah_redeem' => 'integer',
    ];

    public function hadiah()
    {
        return $this->hasMany(StampProgramHadiah::class);
    }

    public function memberStamps()
    {
        return $this->hasMany(MemberStamp::class);
    }

    public function redemptions()
    {
        return $this->hasMany(StampRedemption::class);
    }

    public function getMaksimalRedeemTercapaiAttribute(): bool
    {
        if (is_null($this->maksimal_redeem)) return false;
        return $this->jumlah_redeem >= $this->maksimal_redeem;
    }

    public function getStatusSaatIniAttribute(): string
    {
        $now = now()->startOfDay();

        if (!$this->status_aktif) return 'Nonaktif';
        if ($this->tanggal_mulai && $now->lt($this->tanggal_mulai)) return 'Belum Mulai';
        if ($this->tanggal_berakhir && $now->gt($this->tanggal_berakhir)) return 'Berakhir';
        if ($this->maksimal_redeem_tercapai) return 'Kuota Habis';

        return 'Aktif';
    }

    /**
     * Program stamp yang sedang berjalan SEKARANG (dipakai kasir & member portal).
     * Karena aturannya cuma boleh 1 aktif, query ini cukup ->first().
     */
    public static function aktifSekarang(): ?self
    {
        $now = now()->startOfDay();

        return self::where('status_aktif', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('tanggal_berakhir')->orWhere('tanggal_berakhir', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('maksimal_redeem')->orWhereColumn('jumlah_redeem', '<', 'maksimal_redeem');
            })
            ->first();
    }

    /**
     * Stamp yang didapat dari satu transaksi.
     *
     * Dihitung dari TOTAL AKHIR (sesudah diskon/voucher, termasuk pajak), bukan subtotal
     * sebelum diskon, dan maksimal 1 stamp per transaksi.
     * Contoh (minimal_pembelian 25.000): total akhir 50.000 => 1 stamp, 10.000 => 0 stamp.
     */
    public function hitungStampDidapat(float|int|string|null $totalAkhir): int
    {
        $minimal = (float) $this->minimal_pembelian;

        if ($minimal <= 0) {
            return 0;
        }

        return ((float) $totalAkhir) >= $minimal ? 1 : 0;
    }

    protected static function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_program', 'minimal_pembelian', 'jumlah_stamp_dibutuhkan', 'status_aktif'])
            ->logOnlyDirty()
            ->useLogName('stamp_program');
    }
}