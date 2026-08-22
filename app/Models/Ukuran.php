<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ukuran extends Model
{
    protected $table = 'ukurans';

    protected $fillable = [
        'nama_ukuran',
        'urutan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('nama_ukuran');
    }
}