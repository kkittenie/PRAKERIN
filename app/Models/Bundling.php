<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Bundling extends Model
{
    /** Isi paket ditentukan admin, pembeli tidak memilih. */
    public const JENIS_TETAP = 'tetap';

    /** Admin menentukan daftar produk + varian yang boleh dipilih, pembeli memilih sendiri sejumlah total isi. */
    public const JENIS_BEBAS = 'bebas';

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

    public function isBebas(): bool
    {
        return $this->jenis_produk === self::JENIS_BEBAS;
    }

    public function isTetap(): bool
    {
        return !$this->isBebas();
    }

    /** Total isi yang harus dipilih pembeli pada bundling bebas (cadangan: jumlah qty isi). */
    public function targetQtyBebas(): int
    {
        $target = (int) $this->total_qty_khusus;

        return $target > 0 ? $target : (int) $this->items()->sum('qty');
    }

    /**
     * Ubah pilihan pembeli/kasir jadi peta [bundling_item_id => ['qty' => int, 'catatan' => ?string]].
     * Tidak memvalidasi total. Pilihan lama yang hanya membawa produk_id dipetakan ke
     * baris isi pertama dari produk itu.
     */
    public function petaPilihanBebas(array $pilihan): array
    {
        $this->loadMissing('items');
        $peta = [];

        foreach ($pilihan as $p) {
            $qty = (int) ($p['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $item = null;
            if (!empty($p['item_id'])) {
                $item = $this->items->firstWhere('id', (int) $p['item_id']);
            } elseif (!empty($p['produk_id'])) {
                $item = $this->items->firstWhere('produk_id', (int) $p['produk_id']);
            }

            if (!$item) {
                continue;
            }

            $peta[$item->id] = [
                'qty' => ($peta[$item->id]['qty'] ?? 0) + $qty,
                'catatan' => filled($p['catatan'] ?? null) ? $p['catatan'] : ($peta[$item->id]['catatan'] ?? null),
            ];
        }

        return $peta;
    }

    /**
     * Validasi pilihan untuk bundling bebas: hanya baris isi milik bundling ini, dan total
     * qty harus pas dengan total isi. Mengembalikan daftar bersih
     * [['item_id', 'produk_id', 'detail_produk_id', 'qty', 'catatan'], ...].
     * Untuk bundling tetap, hasilnya selalu kosong.
     *
     * @throws ValidationException
     */
    public function validasiPilihanBebas(array $pilihan, string $kunciError = 'pilihan_khusus'): array
    {
        if (!$this->isBebas()) {
            return [];
        }

        $this->loadMissing('items');

        foreach ($pilihan as $p) {
            if ((int) ($p['qty'] ?? 0) <= 0) {
                continue;
            }
            $cocok = !empty($p['item_id'])
                ? $this->items->firstWhere('id', (int) $p['item_id'])
                : (!empty($p['produk_id']) ? $this->items->firstWhere('produk_id', (int) $p['produk_id']) : null);

            if (!$cocok) {
                throw ValidationException::withMessages([$kunciError => 'Ada pilihan yang bukan bagian dari bundling ini.']);
            }
        }

        $peta = $this->petaPilihanBebas($pilihan);
        $total = array_sum(array_column($peta, 'qty'));
        $target = $this->targetQtyBebas();

        if ($total !== $target) {
            throw ValidationException::withMessages([
                $kunciError => "Total pilihan harus pas {$target} pcs (sekarang {$total} pcs).",
            ]);
        }

        $hasil = [];
        foreach ($peta as $itemId => $data) {
            $item = $this->items->firstWhere('id', $itemId);
            $hasil[] = [
                'item_id' => (int) $itemId,
                'produk_id' => (int) $item->produk_id,
                'detail_produk_id' => $item->detail_produk_id ? (int) $item->detail_produk_id : null,
                'qty' => (int) $data['qty'],
                'catatan' => $data['catatan'],
            ];
        }

        return $hasil;
    }

    public function getJumlahProdukAttribute(): int
    {
        if ($this->isBebas()) {
            return $this->targetQtyBebas();
        }

        return $this->relationLoaded('items')
            ? (int) $this->items->sum('qty')
            : (int) $this->items()->sum('qty');
    }

    public function getPerluDiproduksiAttribute(): bool
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with(['detailProduk', 'produk.detailProduks'])->get();

        // Bundling bebas: pembeli bebas memilih, jadi yang dihitung total stok semua
        // pilihan yang aktif dibanding total isi. Satu pilihan habis tidak membuatnya "perlu diproduksi".
        if ($this->isBebas()) {
            $stokTotal = $items->sum(function ($item) {
                $detail = $item->relationLoaded('detailProduk') ? $item->detailProduk : $item->detailProduk()->first();

                return ($detail && $detail->status_aktif) ? max(0, (int) $detail->stok) : 0;
            });

            return $stokTotal < $this->targetQtyBebas();
        }

        foreach ($items as $item) {
            // Sistem baru: cek stok langsung dari DetailProduk
            if ($item->detail_produk_id) {
                $varian = $item->relationLoaded('detailProduk')
                    ? $item->detailProduk
                    : $item->detailProduk()->first();

                if (!$varian || !$varian->status_aktif || $varian->perlu_diproduksi || (int) $varian->stok < (int) $item->qty) {
                    return true;
                }
                continue;
            }

            // Fallback: sistem lama (ukuran + varian_rasa string)
            $produk = $item->produk;
            if (!$produk) {
                continue;
            }
            $stok = $produk->stokUntukUkuranRasa($item->ukuran, $item->varian_rasa);
            if ($produk->status_ketersediaan === 'perlu_diproduksi' || $stok <= 0 || $stok < (float) $item->qty) {
                return true;
            }
        }

        return false;
    }
}