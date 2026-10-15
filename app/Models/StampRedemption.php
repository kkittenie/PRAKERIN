<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StampRedemption extends Model
{
    protected $table = 'stamp_redemptions';

    protected $fillable = [
        'member_id', 'stamp_program_id', 'produk_id', 'detail_produk_id', 'kode_redeem',
        'sumber', 'status', 'diproses_oleh', 'diambil_pada',
        'transaksi_id', 'jumlah',
    ];

    protected $casts = [
        'diambil_pada' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StampRedemption $redemption) {
            $redemption->kode_redeem ??= self::buatKodeUnik();
        });
    }

    public static function buatKodeUnik(): string
    {
        do {
            $kode = 'STP-' . strtoupper(Str::random(5));
        } while (self::where('kode_redeem', $kode)->exists());

        return $kode;
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function stampProgram()
    {
        return $this->belongsTo(StampProgram::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function detailProduk()
    {
        return $this->belongsTo(DetailProduk::class, 'detail_produk_id');
    }

    /** Nama produk + varian/ukuran hadiah yang ditukar. */
    public function getNamaTampilAttribute(): string
    {
        $nama = $this->produk->nama_produk ?? 'Produk tidak tersedia';
        $varian = $this->detailProduk?->nama_lengkap;

        return $varian ? "{$nama} ({$varian})" : $nama;
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(\App\Models\User::class, 'diproses_oleh');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}