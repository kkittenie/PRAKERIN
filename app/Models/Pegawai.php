<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $fillable = [
    'nama',
    'no_hp',
    'alamat',
    'jabatan',
    'tanggal_masuk',
    'status_aktif',
    'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
