<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Reward extends Model
{
    use LogsActivity;

    protected $table = 'rewards';

    protected $fillable = [
        'nama_reward', 'tipe_reward', 'produk_id', 'poin_dibutuhkan',
        'stok_reward', 'maksimal_redeem', 'jumlah_redeem',
        'tanggal_mulai', 'tanggal_berakhir', 'status_aktif',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
        'status_aktif' => 'boolean',
        'poin_dibutuhkan' => 'integer',
        'stok_reward' => 'integer',
        'maksimal_redeem' => 'integer',
        'jumlah_redeem' => 'integer',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function getStokHabisAttribute(): bool
    {
        return $this->stok_reward <= 0;
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
        if ($this->stok_habis || $this->maksimal_redeem_tercapai) return 'Habis';

        return 'Aktif';
    }

    protected static function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_reward', 'produk_id', 'poin_dibutuhkan', 'stok_reward', 'maksimal_redeem', 'status_aktif'])
            ->logOnlyDirty()
            ->useLogName('reward');
    }
}