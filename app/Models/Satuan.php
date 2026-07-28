<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $fillable = [
        'nama'
    ];

    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class, 'satuan_id');
    }
}