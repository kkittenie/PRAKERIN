<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';

    protected $fillable = [
        'kategori_id',
        'kode_produk',
        'nama_produk',
        'deskripsi',
        'harga_jual',
        'harga_modal',
        'stok',
        'stok_besar',
        'stok_kecil',
        'stok_minimum',
        'foto',
        'status_aktif',
        'status_ketersediaan',
        'punya_ukuran',
        'harga_besar',
        'harga_kecil',
    ];

    protected $casts = [
        'punya_ukuran' => 'boolean',
        'harga_besar' => 'decimal:2',
        'harga_kecil' => 'decimal:2',
        'stok_besar' => 'integer',
        'stok_kecil' => 'integer',
    ];

    protected $appends = [
        'promo_aktif',
        'promos_aktif_semua',
        'harga_diskon',
        'daftar_ukuran',
    ];

    public function scopeReguler($query)
    {
        return $query->where('is_khusus_bundling', false);
    }

    public function scopeKhususBundling($query)
    {
        return $query->where('is_khusus_bundling', true);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

    public function produkUkurans()
    {
        return $this->hasMany(ProdukUkuran::class, 'produk_id')->with('ukuran')->orderBy('id');
    }

    public function produksi()
    {
        return $this->hasMany(Produksi::class);
    }

    public function promos()
    {
        return $this->belongsToMany(Promo::class, 'promo_produk', 'produk_id', 'promo_id')->withTimestamps();
    }

    public function getPromosAktifSemuaAttribute()
    {
        $now = now();
        $isPromoBerlaku = function ($promo) use ($now) {
            if (!$promo->status_aktif) return false;
            if ($promo->mulai_berlaku > $now) return false;
            if ($promo->selesai_berlaku && $promo->selesai_berlaku < $now) return false;
            return true;
        };

        if ($this->relationLoaded('promos')) {
            return $this->promos->filter($isPromoBerlaku)->values()->loadMissing('ukurans');
        }

        return $this->promos()
            ->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            })
            ->with('ukurans')
            ->get();
    }

    public function getPromoAktifAttribute()
    {
        $now = now();
        $isPromoBerlaku = function ($promo) use ($now) {
            if (!$promo->status_aktif) return false;
            if ($promo->mulai_berlaku > $now) return false;
            if ($promo->selesai_berlaku && $promo->selesai_berlaku < $now) return false;
            return true;
        };

        if ($this->relationLoaded('promos')) {
            return $this->promos->filter($isPromoBerlaku)->sortByDesc(function ($p) {
                return $p->jenis_potongan === 'nominal'
                    ? $p->nominal_potongan
                    : (($p->persentase ?? 0) / 100 * $this->getHarga());
            })->first()?->loadMissing('ukurans');
        }

        return $this->promos()
            ->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            })
            ->with('ukurans')
            ->get()
            ->sortByDesc(function ($p) {
                return $p->jenis_potongan === 'nominal'
                    ? $p->nominal_potongan
                    : (($p->persentase ?? 0) / 100 * $this->getHarga());
            })
            ->first();
    }

    public function getHargaDiskonAttribute()
    {
        return $this->hargaSetelahPromo();
    }

    public function hargaSetelahPromo(?string $ukuran = null): float
    {
        $hargaDasar = $this->getHarga($ukuran);
        $promo = $this->promoUntukHitungHarga($ukuran);
        if ($promo) {
            if ($promo->jenis_potongan === 'nominal') {
                $diskon = $promo->nominal_potongan;
            } else {
                $diskon = (($promo->persentase ?? 0) / 100) * $hargaDasar;
            }
            return max(0, $hargaDasar - $diskon);
        }
        return $hargaDasar;
    }

    /**
     * Promo yang beneran berlaku buat ukuran tertentu. Kalau produk gak
     * punya ukuran, tetap pakai promo_aktif (perilaku lama). Kalau punya
     * ukuran, promo yang "dibatasi ukuran" (lewat menu Potongan Harga)
     * cuma dianggap berlaku kalau ukuran ini termasuk yang dicentang admin.
     */
    protected function promoUntukHitungHarga(?string $ukuran): ?Promo
    {
        if (!$this->punya_ukuran) {
            return $this->promo_aktif;
        }

        $ukuranId = optional($this->cariBarisUkuran($ukuran))->ukuran_id;
        return $this->promoAktifUntukUkuran($ukuranId);
    }

    /**
     * Sama kayak accessor promo_aktif, tapi ngecek juga apakah promo itu
     * dibatasi ke ukuran-ukuran tertentu (tabel promo_ukurans). Promo yang
     * gak dibatasi (pivot-nya kosong) tetap dianggap berlaku ke semua
     * ukuran, sama kayak sebelum fitur ini ada.
     */
    public function promoAktifUntukUkuran(?int $ukuranId): ?Promo
    {
        $now = now();
        $promos = $this->relationLoaded('promos') ? $this->promos : $this->promos()->get();

        return $promos->filter(function ($promo) use ($now, $ukuranId) {
            if (!$promo->status_aktif) return false;
            if ($promo->mulai_berlaku > $now) return false;
            if ($promo->selesai_berlaku && $promo->selesai_berlaku < $now) return false;
            return $promo->berlakuUntukUkuran($ukuranId);
        })->sortByDesc(function ($p) {
            return $p->jenis_potongan === 'nominal'
                ? $p->nominal_potongan
                : (($p->persentase ?? 0) / 100 * $this->getHarga());
        })->first();
    }

    public function promoProdukEvents()
    {
        return $this->hasMany(PromoProdukEvent::class);
    }

    /**
     * Ambil koleksi baris produk_ukurans yang aktif buat produk ini, tanpa
     * bikin query baru kalau relasinya udah di-eager-load (mis. dari
     * TransaksiKasirController::indeks()).
     */
    protected function daftarBarisUkuranAktif()
    {
        $daftar = $this->relationLoaded('produkUkurans')
            ? $this->produkUkurans
            : $this->produkUkurans()->get();

        return $daftar->filter(fn ($pu) => (bool) $pu->status_aktif && $pu->ukuran && (bool) $pu->ukuran->status_aktif);
    }

    /**
     * Cari baris produk_ukurans yang namanya cocok (case-insensitive) sama
     * $ukuran. Ini yang bikin sistem ukuran gak lagi kebatas cuma
     * "Besar"/"Kecil" — nama apapun yang didaftarin di menu Ukuran otomatis
     * kepakai di sini.
     */
    protected function cariBarisUkuran(?string $ukuran): ?ProdukUkuran
    {
        if (!$ukuran || !$this->punya_ukuran) {
            return null;
        }

        return $this->daftarBarisUkuranAktif()
            ->first(fn ($pu) => strcasecmp($pu->ukuran->nama_ukuran, $ukuran) === 0);
    }

    /**
     * Daftar ukuran aktif produk ini dalam bentuk array polos, otomatis
     * ke-serialize sebagai "daftar_ukuran" di JSON (dipakai kasir & mobile
     * buat nampilin tombol ukuran secara dinamis).
     */
    public function getDaftarUkuranAttribute(): array
    {
        if (!$this->punya_ukuran) {
            return [];
        }

        return $this->daftarBarisUkuranAktif()
            ->sortBy(fn ($pu) => $pu->ukuran->urutan ?? 0)
            ->map(function ($pu) {
                $hargaAsli = (float) $pu->harga_jual;
                $promo = $this->promoAktifUntukUkuran($pu->ukuran_id);
                $hargaDiskon = $hargaAsli;

                if ($promo) {
                    $diskon = $promo->jenis_potongan === 'nominal'
                        ? $promo->nominal_potongan
                        : (($promo->persentase ?? 0) / 100) * $hargaAsli;
                    $hargaDiskon = max(0, $hargaAsli - $diskon);
                }

                return [
                    'produk_ukuran_id' => $pu->id,
                    'ukuran_id' => $pu->ukuran_id,
                    'nama' => $pu->ukuran->nama_ukuran,
                    'harga' => $hargaAsli,
                    'harga_asli' => $hargaAsli,
                    'harga_diskon' => $hargaDiskon,
                    'sedang_promo' => (bool) $promo,
                    'nama_promo' => $promo?->nama_promo,
                    'harga_modal' => (float) $pu->harga_modal,
                    'stok' => (int) $pu->stok,
                    'stok_minimum' => (int) $pu->stok_minimum,
                    'perlu_produksi' => $pu->status_ketersediaan === 'perlu_diproduksi' || $pu->stok <= 0,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Baris ukuran aktif dengan harga paling rendah. Dipakai buat nampilin
     * harga "mulai dari" di kartu menu/listing — sengaja gak lagi
     * ngasumsiin ukuran termurah itu pasti yang namanya "Kecil", karena
     * urutan harga antar ukuran custom bisa aja beda-beda.
     */
    public function ukuranTermurah(): ?array
    {
        if (!$this->punya_ukuran) {
            return null;
        }

        $daftar = $this->daftar_ukuran;
        if (empty($daftar)) {
            return null;
        }

        return collect($daftar)->sortBy('harga')->first();
    }

    /**
     * Nama ukuran default yang aman dipakai kalau request gak nyertain
     * parameter ukuran (mis. JS gagal kirim). Dulu di-hardcode 'Kecil',
     * yang keliru buat produk yang cuma punya ukuran custom (misal cuma
     * "Jumbo" doang, gak ada baris "Kecil"). Sekarang pakai ukuran aktif
     * termurah yang beneran ada di produk ini.
     */
    public function ukuranDefault(): ?string
    {
        return $this->ukuranTermurah()['nama'] ?? null;
    }

    public function getHarga(?string $ukuran = null): float
    {
        if ($this->punya_ukuran) {
            $baris = $this->cariBarisUkuran($ukuran);
            if ($baris) {
                return (float) $baris->harga_jual;
            }

            // Fallback ke kolom lama — jaga-jaga kalau baris produk_ukurans
            // untuk ukuran ini belum ada (data lama yang belum termigrasi).
            if ($ukuran === 'Besar') {
                return (float) ($this->harga_besar ?? 0);
            }
            if ($ukuran === 'Kecil') {
                return (float) ($this->harga_kecil ?? 0);
            }
            return (float) ($this->harga_kecil ?? $this->harga_besar ?? $this->harga_jual ?? 0);
        }

        return (float) ($this->harga_jual ?? 0);
    }

    public function stokUntukUkuran(?string $ukuran = null): int
    {
        if ($this->punya_ukuran) {
            $baris = $this->cariBarisUkuran($ukuran);
            if ($baris) {
                return (int) $baris->stok;
            }

            if ($ukuran === 'Besar') {
                return (int) ($this->stok_besar ?? 0);
            }
            if ($ukuran === 'Kecil') {
                return (int) ($this->stok_kecil ?? 0);
            }

            // Gak nyebutin ukuran spesifik -> total semua ukuran aktif.
            $totalDinamis = (int) $this->daftarBarisUkuranAktif()->sum('stok');
            if ($totalDinamis > 0) {
                return $totalDinamis;
            }
            return (int) ($this->stok_besar ?? 0) + (int) ($this->stok_kecil ?? 0);
        }

        return (int) $this->stok;
    }

    public function habisUntukUkuran(?string $ukuran = null): bool
    {
        return $this->status_ketersediaan === 'perlu_diproduksi' || $this->stokUntukUkuran($ukuran) <= 0;
    }

    public function habisSemuaUkuran(): bool
    {
        if (!$this->punya_ukuran) {
            return $this->habisUntukUkuran();
        }

        if ($this->status_ketersediaan === 'perlu_diproduksi') {
            return true;
        }

        $daftarAktif = $this->daftarBarisUkuranAktif();
        if ($daftarAktif->isNotEmpty()) {
            return $daftarAktif->every(fn ($pu) => (int) $pu->stok <= 0);
        }

        // Fallback lama, buat produk yang belum punya baris produk_ukurans.
        return (int) ($this->stok_besar ?? 0) <= 0 && (int) ($this->stok_kecil ?? 0) <= 0;
    }

    /**
     * Sumber kebenaran tunggal buat ngurangin stok per-ukuran. Otomatis
     * ngurangin baris produk_ukurans yang cocok (ukuran apapun, gak cuma
     * Besar/Kecil) SEKALIGUS kolom lama stok_besar/stok_kecil/stok kalau
     * namanya "Besar"/"Kecil" — supaya data lama & baru tetap sinkron
     * tanpa perlu ada sinkronisasi manual lagi di controller lain.
     */
    public function decrementStokUkuran(?string $ukuran, int $jumlah): void
    {
        if ($jumlah <= 0) {
            return;
        }

        if (!$this->punya_ukuran) {
            $this->decrement('stok', $jumlah);
            return;
        }

        $baris = $this->cariBarisUkuran($ukuran);
        if ($baris) {
            $baris->stok = max(0, $baris->stok - $jumlah);
            $baris->status_ketersediaan = $baris->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
            $baris->save();
        }

        if ($ukuran === 'Besar') {
            $this->decrement('stok_besar', $jumlah);
        } elseif ($ukuran === 'Kecil') {
            $this->decrement('stok_kecil', $jumlah);
        }

        $this->decrement('stok', $jumlah);
    }

    public function incrementStokUkuran(?string $ukuran, int $jumlah): void
    {
        if ($jumlah <= 0) {
            return;
        }

        if (!$this->punya_ukuran) {
            $this->increment('stok', $jumlah);
            return;
        }

        $baris = $this->cariBarisUkuran($ukuran);
        if ($baris) {
            $baris->stok = $baris->stok + $jumlah;
            $baris->status_ketersediaan = $baris->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
            $baris->save();
        }

        if ($ukuran === 'Besar') {
            $this->increment('stok_besar', $jumlah);
        } elseif ($ukuran === 'Kecil') {
            $this->increment('stok_kecil', $jumlah);
        }

        $this->increment('stok', $jumlah);
    }
}