<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BundlingItem extends Model
{
    protected $table = 'bundling_items';

    protected $fillable = [
        'bundling_id',
        'produk_id',
        'detail_produk_id',
        'ukuran',
        'varian_rasa',
        'qty',
    ];

    public function bundling()
    {
        return $this->belongsTo(Bundling::class, 'bundling_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function detailProduk()
    {
        return $this->belongsTo(DetailProduk::class, 'detail_produk_id');
    }

    /** Bandingkan dua nama ukuran (abaikan spasi dan huruf besar/kecil). Kosong dianggap sama dengan kosong. */
    public static function samaUkuran($a, $b): bool
    {
        return mb_strtolower(trim((string) $a)) === mb_strtolower(trim((string) $b));
    }

    /**
     * Rasa yang boleh dipilih pembeli pada baris isi bundling bebas: semua detail produk
     * aktif milik produk ini dengan ukuran yang sama dengan ukuran baris ini.
     * (Pada bundling bebas satu baris isi = produk + ukuran, rasanya dipilih pembeli.)
     */
    public function opsiRasa(): Collection
    {
        $produk = $this->produk;
        if (!$produk) {
            return collect();
        }

        return $produk->daftarDetail()
            ->filter(fn (DetailProduk $d) => $d->status_aktif && static::samaUkuran($d->ukuran, $this->ukuran))
            ->values();
    }

    /** Data ringan opsi rasa untuk dikirim ke JavaScript (kasir). */
    public function opsiRasaData(): array
    {
        return $this->opsiRasa()->map(fn (DetailProduk $d) => [
            'id' => $d->id,
            'ukuran' => $d->ukuran,
            'variasi' => $d->variasi,
            'nama' => $d->variasi ?: 'Standar',
            'stok' => max(0, (int) $d->stok),
        ])->all();
    }
}