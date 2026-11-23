<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoProdukEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'kategori_id',
        'nama_event',
        'isi_normal',
        'isi_event',
        'harga_event',
        'mulai_berlaku',
        'selesai_berlaku',
        'status_aktif',
    ];

    protected $casts = [
        'isi_normal' => 'integer',
        'isi_event' => 'integer',
        'harga_event' => 'decimal:2',
        'mulai_berlaku' => 'datetime',
        'selesai_berlaku' => 'datetime',
        'status_aktif' => 'boolean',
    ];

    protected $appends = [
        'stok_tersedia',
        'perlu_diproduksi',
        'foto',
        'harga_diskon',
        'harga_asli',
        'hemat_nominal',
        'hemat_persen',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

    public function detailProduks()
    {
        return $this->belongsToMany(DetailProduk::class, 'promo_produk_event_detail_produks', 'promo_produk_event_id', 'detail_produk_id')->withTimestamps();
    }

    public function varianProduks()
    {
        return $this->belongsToMany(VarianProduk::class, 'promo_produk_event_detail_produks', 'promo_produk_event_id', 'varian_produk_id')->withTimestamps();
    }

    /**
     * ID detail produk yang dibatasi buat event ini. Kosong = gak dibatasi
     * (berlaku ke semua detail aktif produk ini).
     */
    public function detailIdsTerbatas(): \Illuminate\Support\Collection
    {
        $detail = $this->relationLoaded('detailProduks')
            ? $this->detailProduks
            : $this->detailProduks()->get();

        return $detail->pluck('id')->map(fn ($id) => (int) $id)->values();
    }

    public function varianIdsTerbatas(): \Illuminate\Support\Collection
    {
        $varian = $this->relationLoaded('varianProduks')
            ? $this->varianProduks
            : $this->varianProduks()->get();

        return $varian->pluck('id')->map(fn ($id) => (int) $id)->values();
    }

    /**
     * Detail produk aktif yang boleh dipakai buat memenuhi event ini, urut sesuai
     * daftar detail produk. Event dibatasi → hanya detail yang dipilih; tidak
     * dibatasi → semua detail aktif produk.
     */
    public function detailYangBerlaku(): \Illuminate\Support\Collection
    {
        if (! $this->produk) {
            return collect();
        }

        if ($this->produk->is_global && $this->produk->jumlahVarianBersama() > 0) {
            $aktif = $this->produk->varianBersamaAktifTersedia();
            $terbatas = $this->varianIdsTerbatas();

            return $terbatas->isEmpty()
                ? $aktif->values()
                : $aktif->filter(fn ($v) => $terbatas->contains((int) $v->id))->values();
        }

        $aktif = $this->produk->daftarDetailAktif();
        $terbatas = $this->detailIdsTerbatas();

        return $terbatas->isEmpty()
            ? $aktif->values()
            : $aktif->filter(fn ($d) => $terbatas->contains((int) $d->id))->values();
    }

    public function berlakuUntukDetail(?DetailProduk $detail, ?VarianProduk $varianProduk = null): bool
    {
        if ($this->produk && $this->produk->is_global && $this->produk->jumlahVarianBersama() > 0) {
            $terbatas = $this->varianIdsTerbatas();
            if ($terbatas->isEmpty()) {
                return true;
            }
            return $varianProduk !== null && $terbatas->contains((int) $varianProduk->id);
        }

        $terbatas = $this->detailIdsTerbatas();
        if ($terbatas->isEmpty()) {
            return true;
        }
        return $detail !== null && $terbatas->contains((int) $detail->id);
    }

    /** Detail yang dipilih event ini (apa adanya, termasuk yang nonaktif) — buat halaman detail event. */
    public function detailTerpilih(): \Illuminate\Support\Collection
    {
        if ($this->produk && $this->produk->is_global && $this->produk->jumlahVarianBersama() > 0) {
            $terbatas = $this->varianIdsTerbatas();

            return $terbatas->isEmpty()
                ? collect()
                : $this->produk->varianProduks()->get()->filter(fn ($v) => $terbatas->contains((int) $v->id))->values();
        }

        $terbatas = $this->detailIdsTerbatas();

        return $this->produk && $terbatas->isNotEmpty()
            ? $this->produk->daftarDetail()->filter(fn ($d) => $terbatas->contains((int) $d->id))->values()
            : collect();
    }

    /** Nama unik (tanpa membedakan huruf besar/kecil) dari kolom $kolom milik $detail. */
    protected static function namaUnik(\Illuminate\Support\Collection $detail, string $kolom): \Illuminate\Support\Collection
    {
        return $detail
            ->map(fn ($d) => trim((string) $d->{$kolom}))
            ->filter()
            ->unique(fn ($n) => mb_strtolower($n))
            ->values();
    }

    /**
     * Nama rasa (mis. "Coklat") yang dipakai buat cek & kurangi stok waktu event
     * ini di-checkout. Cuma bisa ditentukan kalau event dibatasi dan semua detail
     * yang dipilih punya TEPAT SATU variasi — selain itu balikin null supaya stok
     * jatuh ke total semua rasa seperti biasa.
     */
    public function rasaTunggalUntukProduksi(): ?string
    {
        if ($this->detailIdsTerbatas()->isEmpty()) {
            return null;
        }

        $rasa = static::namaUnik($this->detailYangBerlaku(), 'variasi');

        return $rasa->count() === 1 ? $rasa->first() : null;
    }

    /**
     * Daftar nama rasa yang boleh dipakai buat memenuhi event ini, urut sesuai
     * daftar detail produk. Kosong kalau detail yang berlaku tidak punya variasi.
     */
    public function kandidatRasaUntukProduksi(): array
    {
        return static::namaUnik($this->detailYangBerlaku(), 'variasi')->all();
    }

    /**
     * Nama ukuran (mis. "Jumbo") yang dipakai buat cek stok & dicatat di detail
     * transaksi waktu event ini di-checkout. Cuma bisa ditentukan kalau event
     * dibatasi dan detail yang dipilih punya TEPAT SATU ukuran — selain itu
     * balikin null supaya pengecekan stok jatuh ke total semua ukuran.
     */
    public function ukuranTunggalUntukProduksi(): ?string
    {
        if ($this->detailIdsTerbatas()->isEmpty()) {
            return null;
        }

        $ukuran = static::namaUnik($this->detailYangBerlaku(), 'ukuran');

        return $ukuran->count() === 1 ? $ukuran->first() : null;
    }

    public function getSedangBerlangsungAttribute(): bool
    {
        $sekarang = now();

        return $this->status_aktif
            && $sekarang->between($this->mulai_berlaku, $this->selesai_berlaku);
    }

    public function scopeBerlaku($query)
    {
        $now = now();

        return $query->where('status_aktif', true)
            ->where('mulai_berlaku', '<=', $now)
            ->where('selesai_berlaku', '>=', $now);
    }

    public function getStokTersediaAttribute(): int
    {
        if (!$this->produk || $this->isi_event <= 0) {
            return 0;
        }

        if ($this->produk->is_global) {
            $stokDasar = (int) $this->produk->stok;
        } else {
            $stokDasar = (int) $this->detailYangBerlaku()->sum('stok');
        }

        return intdiv($stokDasar, (int) $this->isi_event);
    }

    public function getPerluDiproduksiAttribute(): bool
    {
        return $this->stok_tersedia <= 0;
    }

    /**
     * Foto promo event (dari produk utama).
     */
    public function getFotoAttribute(): ?string
    {
        return $this->produk?->foto;
    }

    /**
     * Alias harga diskon / event untuk kompabilitas view.
     */
    public function getHargaDiskonAttribute(): float
    {
        return (float) $this->harga_event;
    }

    /**
     * Harga normal jika produk dibeli satuan dengan jumlah isi event.
     */
    public function getHargaAsliAttribute(): float
    {
        $hargaSatuan = (float) ($this->produk?->harga_jual ?? 0);
        $qty = max(1, (int) ($this->isi_event ?: 1));

        return $hargaSatuan * $qty;
    }

    /**
     * Nominal penghematan promo event dibanding harga normal.
     */
    public function getHematNominalAttribute(): float
    {
        return max(0, $this->harga_asli - (float) $this->harga_event);
    }

    /**
     * Persentase penghematan promo event.
     */
    public function getHematPersenAttribute(): int
    {
        if ($this->harga_asli <= 0) {
            return 0;
        }

        return (int) round(($this->hemat_nominal / $this->harga_asli) * 100);
    }
}