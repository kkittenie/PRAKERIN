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
        'varian_produk_id',
        'ukuran',
        'varian_rasa',
        'rasa_diizinkan',
        'qty',
    ];

    protected $casts = [
        // Id detail produk / varian produk yang boleh dipilih pembeli. null = semua rasa aktif.
        'rasa_diizinkan' => 'array',
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

    public function varianProduk()
    {
        return $this->belongsTo(VarianProduk::class, 'varian_produk_id');
    }

    /** Bandingkan dua nama ukuran (abaikan spasi dan huruf besar/kecil). Kosong dianggap sama dengan kosong. */
    public static function samaUkuran($a, $b): bool
    {
        return mb_strtolower(trim((string) $a)) === mb_strtolower(trim((string) $b));
    }

    /**
     * Rasa yang boleh dipilih pembeli pada baris isi bundling bebas:
     * Jika produk Stok Bersama dengan varian, diambil dari varian_produks.
     * Jika Stok Per Varian, diambil dari detail_produks aktif.
     */
    public function opsiRasa(): Collection
    {
        $produk = $this->produk;
        if (!$produk) {
            return collect();
        }

        if ($produk->is_global && $produk->jumlahVarianBersama() > 0) {
            return $produk->varianBersamaAktifTersedia()
                ->filter(fn (VarianProduk $v) => static::samaUkuran($v->ukuran, $this->ukuran))
                ->when(
                    !empty($this->rasa_diizinkan),
                    fn (Collection $c) => $c->filter(function ($item) {
                        $id = $item instanceof VarianProduk ? $item->id : (is_array($item) ? $item['id'] : $item);
                        return in_array((int) $id, array_map('intval', $this->rasa_diizinkan), true)
                            || in_array('vp_' . $id, $this->rasa_diizinkan, true);
                    })
                )
                ->values();
        }

        return $produk->daftarDetail()
            ->filter(fn (DetailProduk $d) => $d->status_aktif && static::samaUkuran($d->ukuran, $this->ukuran))
            ->when(
                !empty($this->rasa_diizinkan),
                fn (Collection $c) => $c->filter(fn (DetailProduk $d) => in_array((int) $d->id, array_map('intval', $this->rasa_diizinkan), true))
            )
            ->values();
    }

    /** Data ringan opsi rasa untuk dikirim ke JavaScript (kasir). */
    public function opsiRasaData(): array
    {
        $produk = $this->produk;
        if ($produk && $produk->is_global && $produk->jumlahVarianBersama() > 0) {
            $detail = $produk->daftarDetail()->first();
            return $this->opsiRasa()->map(fn (VarianProduk $v) => [
                'id' => $detail?->id,
                'varian_produk_id' => $v->id,
                'ukuran' => $v->ukuran,
                'variasi' => $v->variasi,
                'nama' => $v->variasi ?: 'Standar',
                'stok' => max(0, (int) ($produk->stok ?? 0)),
            ])->all();
        }

        return $this->opsiRasa()->map(fn (DetailProduk $d) => [
            'id' => $d->id,
            'varian_produk_id' => null,
            'ukuran' => $d->ukuran,
            'variasi' => $d->variasi,
            'nama' => $d->variasi ?: 'Standar',
            'stok' => max(0, (int) $d->stok),
        ])->all();
    }
}