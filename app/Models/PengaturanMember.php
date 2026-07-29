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
}
