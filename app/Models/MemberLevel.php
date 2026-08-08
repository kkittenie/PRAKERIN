<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLevel extends Model
{
    protected $table = 'member_levels';

    protected $fillable = ['nama_level', 'minimal_poin', 'warna_badge', 'status_aktif'];

    protected $casts = [
        'minimal_poin' => 'integer',
        'status_aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true)->orderBy('minimal_poin');
    }
}