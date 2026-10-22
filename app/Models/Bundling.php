<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Bundling extends Model
{
    /** Isi paket ditentukan admin, pembeli tidak memilih. */
    public const JENIS_TETAP = 'tetap';

    /**
     * Admin menentukan produk + ukuran yang boleh dipilih. Rasa dipilih sendiri oleh pembeli
     * (dari semua rasa aktif produk + ukuran itu), sejumlah total isi bundling.
     */
    public const JENIS_BEBAS = 'bebas';

    protected $table = 'bundlings';

    protected $fillable = [
        'kode_bundling',
        'nama_bundling',
        'kategori_id',
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

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

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
     * Cocokkan satu pilihan pembeli/kasir ({item_id, detail_produk_id, ...}) ke baris isi bundling
     * dan rasa (detail produk) yang sah. Mengembalikan [BundlingItem, DetailProduk], atau null kalau
     * baris bukan milik bundling ini, atau rasa tidak ada / nonaktif / beda produk / beda ukuran.
     */
    private function cocokkanPilihanBebas(array $p): ?array
    {
        $itemId = (int) ($p['item_id'] ?? 0);
        $detailId = (int) ($p['detail_produk_id'] ?? 0);
        if ($itemId <= 0 || $detailId <= 0) {
            return null;
        }

        $item = $this->items->firstWhere('id', $itemId);
        if (!$item) {
            return null;
        }

        $detail = $item->opsiRasa()->firstWhere('id', $detailId);

        return $detail ? [$item, $detail] : null;
    }

    /**
     * Ubah pilihan pembeli/kasir jadi peta ["{item_id}:{detail_produk_id}" => [item_id, detail_produk_id, qty, catatan]].
     * Pilihan yang tidak sah (lihat cocokkanPilihanBebas) dan qty 0 dibuang. Tidak memvalidasi total.
     * Pilihan lama yang belum membawa detail_produk_id (format sebelum rasa dipilih pembeli) ikut dibuang,
     * sehingga total tidak akan pas dan pembeli diminta memilih ulang.
     */
    public function petaPilihanBebas(array $pilihan): array
    {
        $this->loadMissing('items.produk');
        $peta = [];

        foreach ($pilihan as $p) {
            $qty = (int) ($p['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $cocok = $this->cocokkanPilihanBebas($p);
            if (!$cocok) {
                continue;
            }

            [$item, $detail] = $cocok;
            $kunci = $item->id . ':' . $detail->id;

            $peta[$kunci] = [
                'item_id' => (int) $item->id,
                'detail_produk_id' => (int) $detail->id,
                'qty' => ($peta[$kunci]['qty'] ?? 0) + $qty,
                'catatan' => filled($p['catatan'] ?? null) ? $p['catatan'] : ($peta[$kunci]['catatan'] ?? null),
            ];
        }

        return $peta;
    }

    /** Apakah total pilihan (yang sah) pas dengan total isi bundling bebas. */
    public function pilihanBebasLengkap(array $pilihan): bool
    {
        return array_sum(array_column($this->petaPilihanBebas($pilihan), 'qty')) === $this->targetQtyBebas();
    }

    /**
     * Validasi pilihan untuk bundling bebas: hanya baris isi milik bundling ini, rasa harus aktif dan
     * sesuai produk + ukuran barisnya, dan total qty harus pas dengan total isi. Mengembalikan daftar bersih
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

        $this->loadMissing('items.produk');

        foreach ($pilihan as $p) {
            if ((int) ($p['qty'] ?? 0) <= 0) {
                continue;
            }
            if (!$this->cocokkanPilihanBebas($p)) {
                throw ValidationException::withMessages([$kunciError => 'Ada pilihan rasa yang tidak tersedia untuk bundling ini. Silakan pilih ulang.']);
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
        foreach ($peta as $data) {
            $item = $this->items->firstWhere('id', $data['item_id']);
            $hasil[] = [
                'item_id' => $data['item_id'],
                'produk_id' => (int) $item->produk_id,
                'detail_produk_id' => $data['detail_produk_id'],
                'qty' => (int) $data['qty'],
                'catatan' => $data['catatan'],
            ];
        }

        return $hasil;
    }

    /**
     * Baris isi yang diproses saat bundling dipesan (checkout / kasir), satu elemen per varian yang diambil:
     * [['item' => BundlingItem, 'qty' => int per satu paket, 'catatan' => ?string], ...].
     *  - Tetap : tiap baris isi apa adanya (varian sudah ditentukan admin).
     *  - Bebas : satu elemen per rasa yang dipilih pembeli. 'item' adalah salinan baris isi (di memori saja)
     *            yang detail_produk_id, ukuran, dan varian_rasa-nya sudah diisi rasa terpilih, jadi kode
     *            pemrosesan stok bisa memperlakukannya sama seperti baris tetap.
     */
    public function barisIsi(array $pilihan = []): array
    {
        $this->loadMissing(['items.produk', 'items.detailProduk']);

        if (!$this->isBebas()) {
            return $this->items->map(fn (BundlingItem $bi) => [
                'item' => $bi,
                'qty' => (int) $bi->qty,
                'catatan' => null,
            ])->all();
        }

        $baris = [];
        foreach ($this->petaPilihanBebas($pilihan) as $p) {
            $item = $this->items->firstWhere('id', $p['item_id']);
            $detail = $item->opsiRasa()->firstWhere('id', $p['detail_produk_id']);

            $salinan = $item->replicate();
            $salinan->id = $item->id;

            if ($detail instanceof VarianProduk) {
                $detailProdukUtama = $item->detailProduk ?: $item->produk?->detailProduks()->first();
                $salinan->detail_produk_id = $detailProdukUtama?->id;
                $salinan->varian_produk_id = $detail->id;
                $salinan->ukuran = $detail->ukuran;
                $salinan->varian_rasa = $detail->variasi;
                $salinan->setRelation('produk', $item->produk);
                $salinan->setRelation('detailProduk', $detailProdukUtama);
                $salinan->setRelation('varianProduk', $detail);
            } else {
                $salinan->detail_produk_id = $detail?->id;
                $salinan->ukuran = $detail?->ukuran;
                $salinan->varian_rasa = $detail?->variasi;
                $salinan->setRelation('produk', $item->produk);
                $salinan->setRelation('detailProduk', $detail);
            }

            $baris[] = [
                'item' => $salinan,
                'qty' => (int) $p['qty'],
                'catatan' => $p['catatan'],
            ];
        }

        return $baris;
    }

    /** Total stok semua rasa aktif dari semua baris isi (hanya bermakna untuk bundling bebas). */
    public function stokTotalBebas(): int
    {
        $this->loadMissing('items.produk');

        return (int) $this->items->sum(
            fn (BundlingItem $item) => $item->opsiRasa()->sum(fn (DetailProduk $d) => max(0, (int) $d->stok))
        );
    }

    /**
     * Berapa paket yang masih bisa dipenuhi dari stok saat ini (null = tidak terbatas / belum bisa dihitung).
     * Tiap isi dihitung dari stok detail produknya dibagi qty per paket; isi yang memakai detail produk
     * yang sama digabung supaya stok bersama tidak dihitung ganda. Bundling bebas dihitung dari pilihan
     * rasa pembeli ($pilihan), tanpa pilihan hasilnya null.
     */
    public function maksPaketTersedia(array $pilihan = []): ?int
    {
        if ($this->isBebas() && empty($pilihan)) {
            return null;
        }

        $kebutuhan = [];
        foreach ($this->barisIsi($pilihan) as $baris) {
            $item = $baris['item'];
            $perPaket = (int) $baris['qty'];
            if ($perPaket <= 0) {
                continue;
            }

            if ($item->detail_produk_id) {
                $detail = $item->detailProduk;
                $kunci = 'd' . $item->detail_produk_id;
                $stok = ($detail && $detail->status_aktif) ? max(0, (int) $detail->stok) : 0;
            } else {
                $produk = $item->produk;
                if (!$produk) {
                    continue;
                }
                $kunci = 'p' . $produk->id . '|' . mb_strtolower((string) $item->ukuran) . '|' . mb_strtolower((string) $item->varian_rasa);
                $stok = max(0, (int) $produk->stokUntukUkuranRasa($item->ukuran, $item->varian_rasa));
            }

            $kebutuhan[$kunci]['stok'] = $stok;
            $kebutuhan[$kunci]['perPaket'] = ($kebutuhan[$kunci]['perPaket'] ?? 0) + $perPaket;
        }

        if (empty($kebutuhan)) {
            return null;
        }

        return (int) min(array_map(fn ($k) => intdiv($k['stok'], $k['perPaket']), $kebutuhan));
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
        // Bundling bebas: pembeli bebas memilih rasa, jadi yang dihitung total stok semua rasa
        // aktif dibanding total isi. Satu rasa habis tidak membuatnya "perlu diproduksi".
        if ($this->isBebas()) {
            return $this->stokTotalBebas() < $this->targetQtyBebas();
        }

        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with(['detailProduk', 'produk.detailProduks'])->get();

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