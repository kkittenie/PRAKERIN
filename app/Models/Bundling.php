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
        'jenis_produk',
        'total_qty_khusus',
        'gambar',
    ];

    /**
     * jumlah_produk & perlu_diproduksi bukan kolom database (cuma accessor),
     * jadi harus di-append biar ikut muncul di toArray()/DataTables collection
     * engine — ini yang dipakai buat proses sorting di kolom tersebut.
     */
    protected $appends = [
        'jumlah_produk',
        'perlu_diproduksi',
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
            if ($produk->habisSemuaUkuran() || $produk->stokUntukUkuran() < (float) $item->qty) {
                return true;
            }
        }

        return false;
    }
}