<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Produk extends Model
{
    protected $table = 'produks';

    // Tabel produks hanya berisi informasi umum. Kode, harga, stok, ukuran, dan variasi
    // ada di detail_produks; atribut di bawah ($appends) dihitung dari detail produk.
    protected $fillable = [
        'kategori_id',
        'nama_produk',
        'deskripsi',
        'foto',
        'status_aktif',
        'dijual_satuan',
    ];

    protected $casts = [
        'dijual_satuan' => 'boolean',
    ];

    protected $appends = [
        'kode_produk',
        'harga_jual',
        'harga_modal',
        'stok',
        'stok_minimum',
        'status_ketersediaan',
        'punya_ukuran',
        'punya_varian_rasa',
        'promo_aktif',
        'promos_aktif_semua',
        'harga_diskon',
        'daftar_ukuran',
        'daftar_varian_rasa',
    ];

    /** Produk yang boleh dijual satuan (kasir dan menu mobile). */
    public function scopeDijualSatuan($query)
    {
        return $query->where('dijual_satuan', true);
    }

    /** Produk yang tidak dijual satuan: hanya dipakai sebagai isi bundling. */
    public function scopeTidakDijualSatuan($query)
    {
        return $query->where('dijual_satuan', false);
    }

    /** Produk yang total stok detailnya sudah di bawah atau sama dengan total stok minimum. */
    public function scopeStokMenipis($query)
    {
        return $query->whereRaw(
            '(SELECT COALESCE(SUM(d.stok), 0) FROM detail_produks d WHERE d.produk_id = produks.id)'
            . ' <= (SELECT COALESCE(SUM(d.stok_minimum), 0) FROM detail_produks d WHERE d.produk_id = produks.id)'
        );
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

    /** Semua detail produk (kombinasi ukuran + variasi) milik produk ini. */
    public function detailProduks()
    {
        return $this->hasMany(DetailProduk::class, 'produk_id')->orderBy('urutan')->orderBy('id');
    }

    /** Varian (mis. topping) untuk produk Stok Bersama/global. Harga dan stoknya ikut detail produk utama. */
    public function varianProduks()
    {
        return $this->hasMany(VarianProduk::class, 'produk_id')->orderBy('urutan')->orderBy('id');
    }

    /** Semua detail produk ini (memakai relasi yang sudah di-eager load kalau ada). */
    public function daftarDetail(): Collection
    {
        if ($this->relationLoaded('detailProduks')) {
            return $this->getRelation('detailProduks');
        }

        $this->setRelation('detailProduks', $this->detailProduks()->get());

        return $this->getRelation('detailProduks');
    }

    public function daftarDetailAktif(): Collection
    {
        return $this->daftarDetail()->filter(fn ($d) => (bool) $d->status_aktif)->values();
    }

    /** Lepas cache relasi supaya pembacaan berikutnya mengambil stok terbaru dari database. */
    public function segarkanStok(): void
    {
        $this->unsetRelation('detailProduks');
    }

    /** Kunci stabil sebuah ukuran (nama tanpa spasi pinggir, huruf kecil) — dipakai JS kasir/bundling sebagai kunci stok per ukuran. */
    public static function kunciUkuran(?string $ukuran): ?string
    {
        return filled($ukuran) ? mb_strtolower(trim($ukuran)) : null;
    }

    protected static function samaNama($a, $b): bool
    {
        return strcasecmp(trim((string) $a), trim((string) $b)) === 0;
    }

    public function punyaUkuranDetail(): bool
    {
        return $this->daftarDetail()->contains(fn ($d) => filled($d->ukuran));
    }

    public function punyaVariasiDetail(): bool
    {
        return $this->daftarDetail()->contains(fn ($d) => filled($d->variasi));
    }

    // ------------------------------------------------------------------
    // Atribut yang dulu kolom di tabel produks, sekarang dihitung dari detail produk
    // ------------------------------------------------------------------

    /** Kode detail pertama (produk tanpa varian hanya punya satu kode). */
    public function getKodeProdukAttribute(): ?string
    {
        return $this->daftarDetail()->first()?->kode_produk;
    }

    /** Harga jual termurah di antara detail aktif (atau semua detail kalau tidak ada yang aktif). */
    public function getHargaJualAttribute(): float
    {
        // Produk Tunggal dengan varian: harga termurah di antara varian aktif.
        $hargaVarian = $this->hargaVarianBersamaTermurah();
        if ($hargaVarian) {
            return $hargaVarian['jual'];
        }

        $aktif = $this->daftarDetailAktif();
        $d = ($aktif->isNotEmpty() ? $aktif : $this->daftarDetail())->sortBy('harga_jual')->first();

        return $d ? (float) $d->harga_jual : 0.0;
    }

    public function getHargaModalAttribute(): float
    {
        $hargaVarian = $this->hargaVarianBersamaTermurah();
        if ($hargaVarian) {
            return $hargaVarian['modal'];
        }

        $aktif = $this->daftarDetailAktif();
        $d = ($aktif->isNotEmpty() ? $aktif : $this->daftarDetail())->sortBy('harga_jual')->first();

        return $d ? (float) $d->harga_modal : 0.0;
    }

    /**
     * Produk Tunggal yang punya varian: harga jual & modal varian aktif termurah
     * (atau semua varian kalau tidak ada yang aktif). Null kalau bukan produk seperti itu.
     */
    public function hargaVarianBersamaTermurah(): ?array
    {
        if (! $this->is_global) {
            return null;
        }

        $varian = $this->varianBersamaAktifTersedia();
        if ($varian->isEmpty()) {
            return null;
        }

        $utama = $this->daftarDetail()->first();
        $pakai = $varian->filter(fn ($v) => (bool) $v->status_aktif);
        $termurah = ($pakai->isNotEmpty() ? $pakai : $varian)
            ->sortBy(fn (VarianProduk $v) => $v->hargaJualEfektif($utama))
            ->first();

        return [
            'jual' => $termurah->hargaJualEfektif($utama),
            'modal' => $termurah->hargaModalEfektif($utama),
        ];
    }

    /** Semua harga jual yang berlaku untuk tampilan rentang harga (per varian untuk Produk Tunggal bervarian). */
    public function daftarHargaJualTampil(bool $hanyaAktif = false): Collection
    {
        if ($this->is_global && $this->jumlahVarianBersama() > 0) {
            $utama = $this->daftarDetail()->first();
            $varian = $this->varianBersamaAktifTersedia();

            if ($hanyaAktif) {
                $aktif = $varian->filter(fn (VarianProduk $v) => (bool) $v->status_aktif);
                $varian = $aktif->isNotEmpty() ? $aktif : $varian;
            }

            return $varian
                ->map(fn (VarianProduk $v) => $v->hargaJualEfektif($utama))
                ->values();
        }

        return $this->daftarDetail()->map(fn ($d) => (float) $d->harga_jual)->values();
    }

    public function daftarHargaModalTampil(): Collection
    {
        if ($this->is_global && $this->jumlahVarianBersama() > 0) {
            $utama = $this->daftarDetail()->first();

            return $this->varianBersamaAktifTersedia()
                ->map(fn (VarianProduk $v) => $v->hargaModalEfektif($utama))
                ->values();
        }

        return $this->daftarDetail()->map(fn ($d) => (float) $d->harga_modal)->values();
    }

    /** Total stok semua detail. */
    public function getStokAttribute(): int
    {
        return (int) $this->daftarDetail()->sum('stok');
    }

    public function getStokMinimumAttribute(): int
    {
        return (int) $this->daftarDetail()->sum('stok_minimum');
    }

    /** Perlu diproduksi kalau tidak ada detail aktif yang stoknya masih ada. */
    public function getStatusKetersediaanAttribute(): string
    {
        return $this->daftarDetailAktif()->contains(fn ($d) => (int) $d->stok > 0)
            ? 'stok_ada'
            : 'perlu_diproduksi';
    }

    /**
     * Produk Stok Bersama (global): paling banyak satu detail dan tanpa ukuran & variasi.
     * Varian/topping-nya ada di tabel varian_produks, bukan di detail produk.
     * Aturannya sama dengan penentuan mode "global" di form produk. Selain itu = Stok Per Varian.
     */
    public function getIsGlobalAttribute(): bool
    {
        $detail = $this->detailProduks;

        return $detail->count() <= 1
            && ! $detail->contains(fn ($d) => filled($d->ukuran) || filled($d->variasi));
    }

    public function getPunyaVarianAttribute(): bool
    {
        if ($this->is_global) {
            return $this->jumlahVarianBersama() > 0;
        }

        return $this->punyaUkuranDetail() || $this->punyaVariasiDetail() || $this->daftarDetail()->count() > 1;
    }

    public function getPunyaUkuranAttribute(): bool
    {
        if ($this->is_global) {
            return $this->varianBersamaAktifTersedia()->contains(fn ($v) => filled($v->ukuran));
        }

        return $this->punyaUkuranDetail();
    }

    public function getPunyaVarianRasaAttribute(): bool
    {
        if ($this->is_global) {
            return $this->varianBersamaAktifTersedia()->contains(fn ($v) => filled($v->variasi));
        }

        return $this->punyaVariasiDetail();
    }

    /**
     * Daftar varian produk untuk form pilihan (Produksi, Bundling, Stamp, Voucher, Promo, dll).
     * Jika Stok Bersama dan punya varian: diambil dari varian_produks.
     * Jika Stok Bersama dan tanpa varian: koleksi kosong (produk tanpa varian).
     * Jika Stok Per Varian: diambil dari detail_produks jika punya varian.
     */
    public function daftarVarianPilihan(): Collection
    {
        if ($this->is_global) {
            if ($this->jumlahVarianBersama() === 0) {
                return collect();
            }

            $detailUtama = $this->daftarDetail()->first();
            $stok = (int) $this->stok;

            return $this->varianBersamaAktifTersedia()
                ->map(function (VarianProduk $v) use ($detailUtama, $stok) {
                    $harga = $v->hargaJualEfektif($detailUtama);

                    return [
                    'id' => 'vp_' . $v->id,
                    'raw_id' => $v->id,
                    'is_stok_bersama' => true,
                    'varian_produk_id' => $v->id,
                    'detail_produk_id' => $detailUtama?->id,
                    'ukuran' => $v->ukuran,
                    'variasi' => $v->variasi,
                    'nama' => $v->nama_lengkap ?: 'Standar',
                    'kode_barcode' => $v->kode_produk,
                    'kode_produk' => $v->kode_produk,
                    'harga' => $harga,
                    'harga_jual' => $harga,
                    'stok' => $stok,
                    'aktif' => (bool) $v->status_aktif,
                    'perlu_produksi' => $stok <= 0,
                    ];
                });
        }

        // Stok Per Varian
        $details = $this->daftarDetailAktif();
        if ($details->count() <= 1 && !$this->punyaUkuranDetail() && !$this->punyaVariasiDetail()) {
            return collect();
        }

        return $details->map(fn (DetailProduk $d) => [
            'id' => $d->id,
            'raw_id' => $d->id,
            'is_stok_bersama' => false,
            'varian_produk_id' => null,
            'detail_produk_id' => $d->id,
            'ukuran' => $d->ukuran,
            'variasi' => $d->variasi,
            'nama' => $d->nama_lengkap ?: 'Standar',
            'kode_barcode' => $d->kode_barcode,
            'kode_produk' => $d->kode_produk,
            'harga' => (float) $d->harga_jual,
            'harga_jual' => (float) $d->harga_jual,
            'stok' => (int) $d->stok,
            'aktif' => (bool) $d->status_aktif,
            'perlu_produksi' => (int) $d->stok <= 0,
        ]);
    }

    /**
     * Kurangi total stok sebesar $jumlah (dipakai hadiah stamp, yang tidak memilih ukuran).
     * Detail dengan stok terbanyak dikurangi lebih dulu.
     */
    public function kurangiStokTotal(int $jumlah): void
    {
        $sisa = $jumlah;

        $detail = DetailProduk::where('produk_id', $this->id)->lockForUpdate()->get()->sortByDesc('stok');

        foreach ($detail as $d) {
            if ($sisa <= 0) {
                break;
            }

            $ambil = min((int) $d->stok, $sisa);
            if ($ambil > 0) {
                $d->stok = (int) $d->stok - $ambil;
                $d->save();
                $sisa -= $ambil;
            }
        }

        $this->segarkanStok();
    }

    /**
     * Daftar detail produk untuk kasir (tabel detail_produks), termasuk yang nonaktif
     * (ditandai 'aktif' => false, tampil abu-abu & tidak bisa dipilih). Satu baris =
     * satu kombinasi ukuran + variasi, lengkap dengan harga (sudah termasuk
     * promo), stok, dan foto.
     */
    public function daftarVarianKasir(): array
    {
        $daftar = $this->daftarDetail();

        // Produk Stok Bersama yang punya varian (topping dsb.): satu baris per varian,
        // tapi harga & stok tetap milik satu detail produk yang sama (stok dipakai bersama).
        $varianBersama = $this->varianBersamaAktifTersedia();
        if ($varianBersama->isNotEmpty() && $daftar->count() === 1) {
            return $this->daftarVarianKasirStokBersama($daftar->first(), $varianBersama);
        }

        return $daftar
            ->map(function (DetailProduk $v) {
                $hargaAsli = (float) $v->harga_jual;
                $promo = $this->promoAktifUntukDetail($v);
                $hargaDiskon = $hargaAsli;

                if ($promo) {
                    $diskon = $promo->jenis_potongan === 'nominal'
                        ? (float) $promo->nominal_potongan
                        : (((float) ($promo->persentase ?? 0)) / 100) * $hargaAsli;
                    $hargaDiskon = max(0, $hargaAsli - $diskon);
                }

                return [
                    'id' => $v->id,
                    'aktif' => (bool) $v->status_aktif,
                    'ukuran' => $v->ukuran,
                    'variasi' => $v->variasi,
                    'nama' => $v->nama_lengkap,
                    'kode_produk' => $v->kode_produk,
                    'kode_barcode' => $v->kode_produk,
                    'ukuran_key' => static::kunciUkuran($v->ukuran),
                    'harga' => $hargaAsli,
                    'harga_asli' => $hargaAsli,
                    'harga_diskon' => $hargaDiskon,
                    'sedang_promo' => (bool) $promo,
                    'nama_promo' => $promo?->nama_promo,
                    'stok' => (int) $v->stok,
                    'stok_minimum' => (int) $v->stok_minimum,
                    'foto' => $v->foto_url,
                ];
            })
            ->values()
            ->all();
    }

    /** Varian (tabel varian_produks) milik produk Stok Bersama, semuanya termasuk yang nonaktif. Kosong kalau bukan produk global. */
    public function varianBersamaAktifTersedia(): Collection
    {
        if (! $this->is_global) {
            return collect();
        }

        return $this->relationLoaded('varianProduks')
            ? $this->getRelation('varianProduks')
            : $this->varianProduks()->get();
    }

    /** Jumlah varian untuk produk Stok Bersama (0 kalau bukan produk global atau belum punya varian). */
    public function jumlahVarianBersama(): int
    {
        return $this->varianBersamaAktifTersedia()->count();
    }

    /**
     * Baris kasir/mobile untuk produk Stok Bersama: 'id' tetap id detail produk (pemegang stok & harga),
     * 'varian_produk_id' menunjuk ke varian yang dipilih. Karena 'id'-nya sama, stok otomatis dipakai bersama.
     */
    protected function daftarVarianKasirStokBersama(DetailProduk $detail, Collection $varian): array
    {
        return $varian
            ->map(function (VarianProduk $v) use ($detail) {
                // Harga per varian (stok tetap satu di detail produk utama).
                $hargaAsli = $v->hargaJualEfektif($detail);
                $promo = $this->promoAktifUntukDetail($detail, $v);
                $hargaDiskon = $hargaAsli;

                if ($promo) {
                    $diskon = $promo->jenis_potongan === 'nominal'
                        ? (float) $promo->nominal_potongan
                        : (((float) ($promo->persentase ?? 0)) / 100) * $hargaAsli;
                    $hargaDiskon = max(0, $hargaAsli - $diskon);
                }

                return [
                    'id' => $detail->id,
                    'varian_produk_id' => $v->id,
                    'aktif' => (bool) $detail->status_aktif && (bool) $v->status_aktif,
                    'ukuran' => $v->ukuran,
                    'variasi' => $v->variasi,
                    'nama' => $v->nama_lengkap,
                    'kode_produk' => $v->kode_produk,
                    'kode_barcode' => $v->kode_produk,
                    'ukuran_key' => static::kunciUkuran($v->ukuran),
                    'harga' => $hargaAsli,
                    'harga_asli' => $hargaAsli,
                    'harga_diskon' => $hargaDiskon,
                    'sedang_promo' => (bool) $promo,
                    'nama_promo' => $promo?->nama_promo,
                    'stok' => (int) $detail->stok,
                    'stok_minimum' => (int) $detail->stok_minimum,
                    'foto' => $v->foto_url ?: $detail->foto_url,
                ];
            })
            ->values()
            ->all();
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
            return $this->promos->filter($isPromoBerlaku)->values()->loadMissing('detailProduks');
        }

        return $this->promos()
            ->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            })
            ->with('detailProduks')
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
            })->first()?->loadMissing('detailProduks');
        }

        return $this->promos()
            ->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('selesai_berlaku')
                  ->orWhere('selesai_berlaku', '>=', $now);
            })
            ->with('detailProduks')
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

    public function promoUntukHitungHarga(?string $ukuran): ?Promo
    {
        if (!$this->punyaUkuranDetail()) {
            return $this->promo_aktif;
        }

        $detail = $this->daftarDetailAktif()->first(fn ($d) => static::samaNama($d->ukuran, $ukuran));

        return $this->promoAktifUntukDetail($detail);
    }

    /** Promo aktif terbaik untuk satu detail produk (ukuran + variasi) milik produk ini. */
    public function promoAktifUntukDetail(?DetailProduk $detail, ?VarianProduk $varianProduk = null): ?Promo
    {
        $now = now();
        $promos = $this->relationLoaded('promos') ? $this->promos : $this->promos()->get();
        $promos->loadMissing(['detailProduks', 'varianProduks']);

        return $promos->filter(function ($promo) use ($now, $detail, $varianProduk) {
            if (!$promo->status_aktif) return false;
            if ($promo->mulai_berlaku > $now) return false;
            if ($promo->selesai_berlaku && $promo->selesai_berlaku < $now) return false;
            return $promo->berlakuUntukDetail($this->id, $detail, $varianProduk);
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

    // ------------------------------------------------------------------
    // Harga & stok per ukuran / variasi, dihitung dari detail_produks.
    // Nama method dipertahankan karena bundling, promo, kasir, dan mobile masih memanggilnya.
    // ------------------------------------------------------------------

    public function getDaftarUkuranAttribute(): array
    {
        $aktif = $this->daftarDetailAktif()->filter(fn ($d) => filled($d->ukuran));

        if ($aktif->isEmpty()) {
            return [];
        }

        return $aktif
            ->groupBy(fn ($d) => mb_strtolower(trim($d->ukuran)))
            ->map(function ($grup) {
                $d = $grup->sortBy('harga_jual')->first();
                $hargaAsli = (float) $d->harga_jual;
                $promo = $this->promoAktifUntukDetail($d);
                $hargaDiskon = $hargaAsli;

                if ($promo) {
                    $diskon = $promo->jenis_potongan === 'nominal'
                        ? $promo->nominal_potongan
                        : (($promo->persentase ?? 0) / 100) * $hargaAsli;
                    $hargaDiskon = max(0, $hargaAsli - $diskon);
                }

                $stok = (int) $grup->sum('stok');

                return [
                    'produk_ukuran_id' => $d->id,
                    'ukuran_key' => static::kunciUkuran($d->ukuran),
                    'nama' => $d->ukuran,
                    'harga' => $hargaAsli,
                    'harga_asli' => $hargaAsli,
                    'harga_diskon' => $hargaDiskon,
                    'sedang_promo' => (bool) $promo,
                    'nama_promo' => $promo?->nama_promo,
                    'harga_modal' => (float) $d->harga_modal,
                    'stok' => $stok,
                    'stok_minimum' => (int) $grup->sum('stok_minimum'),
                    'perlu_produksi' => $stok <= 0,
                ];
            })
            ->values()
            ->all();
    }

    public function getDaftarVarianRasaAttribute(): array
    {
        $aktif = $this->daftarDetailAktif()->filter(fn ($d) => filled($d->variasi));

        if ($aktif->isEmpty()) {
            return [];
        }

        return $aktif
            ->groupBy(fn ($d) => mb_strtolower(trim($d->variasi)))
            ->map(function ($grup) {
                $d = $grup->first();

                return [
                    'produk_varian_rasa_id' => $d->id,
                    'nama' => $d->variasi,
                    'gambar' => $grup->first(fn ($x) => $x->foto)?->foto_url,
                    'stok' => (int) $grup->sum('stok'),
                    'stok_minimum' => (int) $grup->sum('stok_minimum'),
                    'stok_per_ukuran' => $grup
                        ->filter(fn ($x) => filled($x->ukuran))
                        ->mapWithKeys(fn ($x) => [static::kunciUkuran($x->ukuran) => (int) $x->stok])
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function ukuranTermurah(): ?array
    {
        $daftar = $this->daftar_ukuran;

        return empty($daftar) ? null : collect($daftar)->sortBy('harga')->first();
    }

    public function ukuranDefault(): ?string
    {
        return $this->ukuranTermurah()['nama'] ?? null;
    }

    public function getHarga(?string $ukuran = null): float
    {
        // Produk Tunggal bervarian: harga dasar = varian aktif termurah, sama dengan harga_jual,
        // supaya harga diskon/promo tidak bergantung pada harga produk utama yang tersembunyi.
        if ($ukuran === null && ($hargaVarian = $this->hargaVarianBersamaTermurah())) {
            return $hargaVarian['jual'];
        }

        $aktif = $this->daftarDetailAktif();

        if ($ukuran !== null) {
            $d = $aktif->filter(fn ($x) => static::samaNama($x->ukuran, $ukuran))->sortBy('harga_jual')->first();
            if ($d) {
                return (float) $d->harga_jual;
            }
        }

        $d = ($aktif->isNotEmpty() ? $aktif : $this->daftarDetail())->sortBy('harga_jual')->first();

        return $d ? (float) $d->harga_jual : (float) ($this->harga_jual ?? 0);
    }

    public function stokUntukUkuran(?string $ukuran = null): int
    {
        $aktif = $this->daftarDetailAktif();

        if ($ukuran !== null) {
            $aktif = $aktif->filter(fn ($d) => static::samaNama($d->ukuran, $ukuran));
        }

        return (int) $aktif->sum('stok');
    }

    /** Stok satu kombinasi (ukuran + variasi). Salah satunya boleh null. */
    public function stokUntukUkuranRasa(?string $ukuran = null, ?string $namaRasa = null): int
    {
        if (!$namaRasa) {
            return $this->stokUntukUkuran($ukuran);
        }

        return (int) $this->daftarDetailAktif()
            ->filter(fn ($d) => static::samaNama($d->variasi, $namaRasa)
                && ($ukuran === null || static::samaNama($d->ukuran, $ukuran)))
            ->sum('stok');
    }

    public function habisUntukUkuran(?string $ukuran = null): bool
    {
        return $this->stokUntukUkuran($ukuran) <= 0;
    }

    public function habisSemuaUkuran(): bool
    {
        $aktif = $this->daftarDetailAktif();

        return $aktif->isEmpty() || $aktif->every(fn ($d) => (int) $d->stok <= 0);
    }

    /** Cari detail untuk diubah stoknya: kombinasi persis, atau (kalau variasi tidak disebut) ukuran yang cocok. */
    protected function cariDetailUntukStok(?string $ukuran, ?string $namaRasa, bool $kurangi): ?DetailProduk
    {
        $semua = DetailProduk::where('produk_id', $this->id)->orderBy('urutan')->orderBy('id')->get();

        $cocok = $semua->filter(function ($d) use ($ukuran, $namaRasa) {
            if ($namaRasa && !static::samaNama($d->variasi, $namaRasa)) {
                return false;
            }
            if ($ukuran !== null && !static::samaNama($d->ukuran, $ukuran)) {
                return false;
            }
            return true;
        });

        if ($cocok->isEmpty()) {
            $cocok = $semua;
        }

        $aktif = $cocok->filter(fn ($d) => (bool) $d->status_aktif);
        $kandidat = $aktif->isNotEmpty() ? $aktif : $cocok;

        return $kurangi
            ? $kandidat->sortByDesc('stok')->first()
            : $kandidat->first();
    }

    protected function ubahStokDetail(?string $ukuran, ?string $namaRasa, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        $detail = $this->cariDetailUntukStok($ukuran, $namaRasa, $delta < 0);

        if ($detail) {
            $detail->stok = max(0, (int) $detail->stok + $delta);
            $detail->status_ketersediaan = $detail->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
            $detail->save();
        }

        $this->segarkanStok();
    }

    public function decrementStokUkuran(?string $ukuran, int $jumlah): void
    {
        if ($jumlah > 0) {
            $this->ubahStokDetail($ukuran, null, -$jumlah);
        }
    }

    public function incrementStokUkuran(?string $ukuran, int $jumlah): void
    {
        if ($jumlah > 0) {
            $this->ubahStokDetail($ukuran, null, $jumlah);
        }
    }

    /** $delta negatif = kurangi (penjualan), positif = tambah (produksi/retur). */
    public function ubahStokRasa(?string $ukuran, ?string $namaRasa, int $delta): void
    {
        $this->ubahStokDetail($ukuran, $namaRasa, $delta);
    }

    public function kurangiStokKombinasi(?string $ukuran, ?string $namaRasa, int $jumlah): void
    {
        if ($jumlah > 0) {
            $this->ubahStokDetail($ukuran, $namaRasa, -$jumlah);
        }
    }

    /** Nama semua variasi aktif produk ini. Kosong kalau produk tidak punya variasi. */
    public function namaRasaAktif(): array
    {
        return collect($this->daftar_varian_rasa)->pluck('nama')->values()->all();
    }

    /**
     * Bagi kebutuhan $jumlah ke stok READY (ukuran x variasi) yang tersedia, sisanya jadi
     * kebutuhan produksi. $kandidatRasa = variasi yang boleh dipakai, urut prioritas.
     * $terpakai = stok yang sudah "diambil" baris lain dalam transaksi yang sama.
     *
     * Return: ['ready' => [['varian_rasa' => ?string, 'jumlah' => int], ...], 'produksi' => int]
     */
    public function alokasiStok(?string $ukuran, array $kandidatRasa, int $jumlah, array &$terpakai = []): array
    {
        if ($jumlah <= 0) {
            return ['ready' => [], 'produksi' => 0];
        }

        $kandidat = $this->punyaVariasiDetail() ? array_values($kandidatRasa) : [null];
        if (empty($kandidat)) {
            $kandidat = [null];
        }

        $sisa = $jumlah;
        $ready = [];

        foreach ($kandidat as $rasa) {
            if ($sisa <= 0) {
                break;
            }

            $kunci = $this->id . '|' . ($ukuran ?? '-') . '|' . strtolower((string) ($rasa ?? '-'));
            $sudahDipakai = (int) ($terpakai[$kunci] ?? 0);
            $stok = max(0, (int) floor((float) $this->stokUntukUkuranRasa($ukuran, $rasa)) - $sudahDipakai);
            $ambil = min($stok, $sisa);

            if ($ambil > 0) {
                $terpakai[$kunci] = $sudahDipakai + $ambil;
                $ready[] = ['varian_rasa' => $rasa, 'jumlah' => $ambil];
                $sisa -= $ambil;
            }
        }

        return ['ready' => $ready, 'produksi' => $sisa];
    }
}