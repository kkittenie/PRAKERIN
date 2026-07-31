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
            'kebutuhan_produksi',
            'user_id',
            'keterangan',
            'kadaluarsa_tipe',
            'kadaluarsa_tanggal',
            'kadaluarsa_jam',
            'jumlah_disesuaikan',
        ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
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
        if ($this->kadaluarsa_tipe === 'jam') {
            if (!$this->kadaluarsa_jam || !$this->jam_produksi) {
                return false;
            }

            $batasWaktu = \Carbon\Carbon::parse(
                \Carbon\Carbon::parse($this->tanggal)->format('Y-m-d') . ' ' . $this->jam_produksi
            )->addHours($this->kadaluarsa_jam);

            return $batasWaktu->isPast();
        }

        if ($this->kadaluarsa_tipe === 'tanggal') {
            if (!$this->kadaluarsa_tanggal) {
                return false;
            }

            return \Carbon\Carbon::parse($this->kadaluarsa_tanggal)->endOfDay()->isPast();
        }

        return false;
    }

    public function getSisaBelumDisesuaikanAttribute(): int
    {
        return max(0, $this->jumlah_hasil - $this->jumlah_disesuaikan);
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
}
