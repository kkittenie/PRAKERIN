<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    public const ASAL_KASIR = 'kasir';
    public const ASAL_MEMBER_APP = 'member_app';

    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SIAP_DIAMBIL = 'siap_diambil';
    public const STATUS_SUDAH_DIAMBIL = 'sudah_diambil';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'kode_transaksi',
        'nama_pembeli',
        'member_id',
        'voucher_id',
        'poin_sebelum',
        'poin_didapat',
        'subtotal',
        'pajak',
        'diskon',
        'diskon_persen',
        'diskon_label',
        'total_bayar',
        'jumlah_diterima',
        'kembalian',
        'metode_pembayaran',
        'status_pembayaran',
        'bukti_pembayaran',
        'bukti_dikonfirmasi_pada',
        'asal_pesanan',
        'status_pesanan',
        'kode_pickup',
        'catatan_pesanan',
        'siap_pada',
        'diambil_pada',
        'diproses_pada',
        'catatan_penolakan',
        'alasan_pembatalan',
    ];

    protected $casts = [
        'siap_pada' => 'datetime',
        'diambil_pada' => 'datetime',
        'bukti_dikonfirmasi_pada' => 'datetime',
        'diproses_pada' => 'datetime',
    ];

    /**
     * Label yang dipakai di UI (Member App maupun POS) untuk tiap status_pesanan.
     */
    public static function labelStatusPesanan(?string $status): string
    {
        return match ($status) {
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_SIAP_DIAMBIL => 'Ready for Pickup',
            self::STATUS_SUDAH_DIAMBIL => 'Sudah Diambil',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
            default => '-',
        };
    }

    public function getIsPickupMemberAttribute(): bool
    {
        return $this->asal_pesanan === self::ASAL_MEMBER_APP;
    }

    /**
     * Kode pesanan 6 digit angka & unik (mis. "483920") untuk menyelesaikan pesanan.
     * Dipakai SEMUA pesanan: dari Member App (online) maupun kasir langsung (offline).
     * Angka saja supaya gampang dibacakan/diketik ulang oleh kasir.
     */
    public static function buatKodePickupUnik(): string
    {
        do {
            $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('kode_pickup', $kode)->exists());

        return $kode;
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function voucher()
    {
        return $this->belongsTo(RewardKatalog::class, 'voucher_id');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }

    public function pesananDitahan()
    {
        return $this->hasOne(PesananDitahan::class, 'transaksi_id');
    }

    public function stampRedemptions()
    {
        return $this->hasMany(StampRedemption::class, 'transaksi_id');
    }
}