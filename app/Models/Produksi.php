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

    public function produksiUkurans()
    {
        return $this->hasMany(ProduksiUkuran::class, 'produksi_id')->with('produkUkuran.ukuran');
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

    public function getRincianUkuranAttribute(): string
    {
        $bagian = [];

        if ($this->jumlah_besar > 0) {
            $bagian[] = $this->jumlah_besar . ' Besar';
        }

        if ($this->jumlah_kecil > 0) {
            $bagian[] = $this->jumlah_kecil . ' Kecil';
        }

        return $bagian ? implode(', ', $bagian) : '-';
    }

    public function getRincianKebutuhanUkuranAttribute(): string
    {
        $bagian = [];

        if ($this->kebutuhan_besar > 0) {
            $bagian[] = $this->kebutuhan_besar . ' Besar';
        }

        if ($this->kebutuhan_kecil > 0) {
            $bagian[] = $this->kebutuhan_kecil . ' Kecil';
        }

        return $bagian ? implode(', ', $bagian) : '-';
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
     * (bukan "X jam setelah produksi"). Contoh: produksi 10:00 + 2 jam -> "15-08-2026 12:00".
     */
    public function getKadaluarsaLabelCetakAttribute(): string
    {
        $waktu = $this->kadaluarsa_waktu;

        if (!$waktu) {
            return '-';
        }

        return $this->kadaluarsa_tipe === 'jam'
            ? $waktu->format('d-m-Y H:i')
            : $waktu->format('d-m-Y');
    }
}