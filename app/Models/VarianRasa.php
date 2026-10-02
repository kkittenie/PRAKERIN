<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VarianRasa extends Model
{
    protected $table = 'varian_rasas';

    protected $fillable = [
        'nama_rasa',
        'gambar',
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
        return $query->orderBy('nama_rasa');
    }
}