<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLevel extends Model
{
    protected $table = 'member_levels';

    protected $fillable = ['nama_level', 'minimal_transaksi', 'warna_badge', 'status_aktif'];

    protected $casts = [
        'minimal_transaksi' => 'decimal:2',
        'status_aktif' => 'boolean',
    ];

    /**
     * Apakah ada minimal 1 level aktif? Kalau semua level dinonaktifkan,
     * fitur tier dianggap mati dan info tier disembunyikan di Member App.
     */
    public static function adaYangAktif(): bool
    {
        return static::where('status_aktif', true)->exists();
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true)->orderBy('minimal_transaksi');
    }
}