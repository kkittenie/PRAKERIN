<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundling extends Model
{
    protected $table = 'bundlings';

    protected $fillable = [
        'kode_bundling',
        'nama_bundling',
        'harga',
        'deskripsi',
    ];

    public function items()
    {
        return $this->hasMany(BundlingItem::class, 'bundling_id');
    }

    public function getJumlahProdukAttribute(): int
    {
        return $this->relationLoaded('items')
            ? $this->items->sum('qty')
            : $this->items()->sum('qty');
    }
}