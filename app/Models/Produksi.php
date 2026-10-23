<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produksi extends Model
{
    protected $table = 'produksis';

    protected $fillable = [
        'tanggal',
        'jam_produksi',
        'produk_id',
        'jumlah_hasil',
        'jumlah_besar',
        'jumlah_kecil',
        'kebutuhan_produksi',
        'kebutuhan_besar',
        'kebutuhan_kecil',
        'user_id',
        'keterangan',
        'kadaluarsa_tipe',
        'kadaluarsa_tanggal',
        'kadaluarsa_jam',
        'status',
        'jumlah_disesuaikan',
    ];

    protected $casts = [
        'jumlah_hasil' => 'integer',
        'jumlah_besar' => 'integer',
        'jumlah_kecil' => 'integer',
        'kebutuhan_produksi' => 'integer',
        'kebutuhan_besar' => 'integer',
        'kebutuhan_kecil' => 'integer',
        'jumlah_disesuaikan' => 'integer',
        'kadaluarsa_jam' => 'integer',
        'kadaluarsa_tanggal' => 'date',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /** Rincian produksi per detail produk (satu baris = satu kombinasi ukuran + variasi). */
    public function produksiVarians()
    {
        return $this->hasMany(ProduksiVarian::class, 'produksi_id')->orderBy('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailBahan()
    {
        return $this->hasMany(ProduksiBahanBaku::class, 'produksi_id');
    }

    public function getIsKadaluarsaAttribute(): bool
    {
        $batasWaktu = $this->kadaluarsa_waktu;

        return $batasWaktu ? $batasWaktu->isPast() : false;
    }

    /**
     * Waktu pasti kadaluarsa (Carbon object). Untuk tipe "jam" dihitung otomatis:
     * tanggal produksi + jam produksi + kadaluarsa_jam.
     */
    public function getKadaluarsaWaktuAttribute(): ?\Carbon\Carbon
    {
        if ($this->kadaluarsa_tipe === 'jam') {
            if (!$this->kadaluarsa_jam || !$this->jam_produksi) {
                return null;
            }

            return \Carbon\Carbon::parse(
                \Carbon\Carbon::parse($this->tanggal)->format('Y-m-d') . ' ' . $this->jam_produksi
            )->addHours((int) $this->kadaluarsa_jam);
        }

        if ($this->kadaluarsa_tipe === 'tanggal') {
            if (!$this->kadaluarsa_tanggal) {
                return null;
            }

            return \Carbon\Carbon::parse($this->kadaluarsa_tanggal)->endOfDay();
        }

        return null;
    }

    /**
     * Ringkasan per ukuran dari produksi_varians. Contoh: "5 Besar, 3 Kecil".
     * "-" kalau detail-detail produksi ini tidak punya ukuran.
     */
    protected function ringkasVarianPerUkuran(string $kolom): string
    {
        $adaUkuran = $this->produksiVarians->contains(fn ($v) => filled($v->ukuran));
        $baris = $this->produksiVarians->filter(fn ($v) => $v->{$kolom} > 0);

        if (!$adaUkuran || $baris->isEmpty()) {
            return '-';
        }

        return $baris
            ->groupBy(fn ($v) => (string) $v->ukuran)
            ->map(fn ($grup, $ukuran) => $grup->sum($kolom) . ' ' . ($ukuran !== '' ? $ukuran : 'Tanpa ukuran'))
            ->implode(', ');
    }

    /**
     * Rincian per variasi, dikelompokkan per ukuran. Contoh:
     * "Kecil: Coklat 3, Keju 2 | Besar: Coklat 1". Null kalau tidak ada variasi.
     */
    protected function kelompokVarian(string $kolom): ?string
    {
        $baris = $this->produksiVarians->filter(fn ($v) => $v->{$kolom} > 0 && filled($v->variasi));

        if ($baris->isEmpty()) {
            return null;
        }

        return $baris
            ->groupBy(fn ($v) => (string) $v->ukuran)
            ->map(function ($grup, $ukuran) use ($kolom) {
                $teks = $grup->map(fn ($v) => $v->variasi . ' ' . $v->{$kolom})->implode(', ');

                return $ukuran !== '' ? $ukuran . ': ' . $teks : $teks;
            })
            ->implode(' | ');
    }

    public function getRincianUkuranAttribute(): string
    {
        return $this->ringkasVarianPerUkuran('jumlah_hasil');
    }

    public function getRincianKebutuhanUkuranAttribute(): string
    {
        return $this->ringkasVarianPerUkuran('kebutuhan');
    }

    public function getRincianRasaAttribute(): string
    {
        return $this->kelompokVarian('jumlah_hasil') ?? '-';
    }

    public function getRincianKebutuhanRasaAttribute(): string
    {
        return $this->kelompokVarian('kebutuhan') ?? '-';
    }

    public function getPunyaRincianRasaAttribute(): bool
    {
        return $this->produksiVarians->contains(fn ($v) => filled($v->variasi));
    }

    public function getJumlahEfektifAttribute(): int
    {
        return max(0, $this->jumlah_hasil - (int) $this->kebutuhan_produksi);
    }

    public function getSisaBelumDisesuaikanAttribute(): int
    {
        return max(0, $this->jumlah_efektif - $this->jumlah_disesuaikan);
    }

    public function getPerluTindakanAttribute(): bool
    {
        return $this->is_kadaluarsa && $this->sisa_belum_disesuaikan > 0;
    }

    public function getKadaluarsaLabelAttribute(): string
    {
        if ($this->kadaluarsa_tipe === 'jam' && $this->kadaluarsa_jam) {
            return $this->kadaluarsa_jam . ' jam setelah produksi';
        }

        if ($this->kadaluarsa_tipe === 'tanggal' && $this->kadaluarsa_tanggal) {
            return \Carbon\Carbon::parse($this->kadaluarsa_tanggal)->format('d-m-Y');
        }

        return '-';
    }

    /**
     * Label kadaluarsa untuk dicetak di label barcode: selalu waktu pasti
     * (bukan "X jam setelah produksi"). Format tanggal ddmmyy.
     * Contoh: tanggal -> "091126"; produksi 10:00 + 2 jam -> "150826 12:00".
     */
    public function getKadaluarsaLabelCetakAttribute(): string
    {
        $waktu = $this->kadaluarsa_waktu;

        if (!$waktu) {
            return '-';
        }

        return $this->kadaluarsa_tipe === 'jam'
            ? $waktu->format('dmy H:i')
            : $waktu->format('dmy');
    }
}