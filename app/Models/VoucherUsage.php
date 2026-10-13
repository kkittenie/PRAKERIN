<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    protected $table = 'voucher_usages';

    protected $fillable = [
        'reward_katalog_id',
        'member_id',
        'transaksi_id',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function voucher()
    {
        return $this->belongsTo(RewardKatalog::class, 'reward_katalog_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}
