<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanMember extends Model
{
    protected $table = 'pengaturan_member';

    protected $fillable = [
        'nama_aturan',
        'minimal_belanja',
        'poin',
        'mulai_berlaku',
        'selesai_berlaku',
        'status_aktif',
    ];

    protected $casts = [
        'mulai_berlaku' => 'datetime',
        'selesai_berlaku' => 'datetime',
        'status_aktif' => 'boolean',
    ];

    public static function aktifSekarang(): ?self
    {
        $now = now();

        return self::where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')->orWhere('selesai_berlaku', '>=', $now);
            })
            ->first();
    }
}