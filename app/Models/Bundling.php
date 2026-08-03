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
        'status_aktif',
        'gambar',
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

    public function getPerluDiproduksiAttribute(): bool
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->with('produk')->get();

        foreach ($items as $item) {
            $produk = $item->produk;
            if (!$produk) {
                continue;
            }
            if ($produk->status_ketersediaan === 'perlu_diproduksi' || (float) $produk->stok <= 0 || (float) $produk->stok < (float) $item->qty) {
                return true;
            }
        }

        return false;
    }
}