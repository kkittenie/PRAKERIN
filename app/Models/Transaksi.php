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

    /**
     * Batas waktu (menit) bagi member untuk upload bukti pembayaran setelah checkout.
     * Lewat dari ini dan belum ada bukti -> pesanan dibatalkan otomatis oleh sistem.
     */
    public const BATAS_BAYAR_MENIT = 10;

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
        'shift_kasir_id',
        'status_pembayaran',
        'bukti_pembayaran',
        'bukti_dikonfirmasi_pada',
        'batas_bayar_pada',
        'asal_pesanan',
        'status_pesanan',
        'kode_pickup',
        'catatan_pesanan',
        'siap_pada',
        'diambil_pada',
        'diproses_pada',
        'stok_dikurangi_pada',
        'catatan_penolakan',
        'alasan_pembatalan',
    ];

    protected $casts = [
        'siap_pada' => 'datetime',
        'diambil_pada' => 'datetime',
        'bukti_dikonfirmasi_pada' => 'datetime',
        'batas_bayar_pada' => 'datetime',
        'diproses_pada' => 'datetime',
        'stok_dikurangi_pada' => 'datetime',
    ];

    /** Shift kasir tempat transaksi ini dibayar. */
    public function shiftKasir()
    {
        return $this->belongsTo(ShiftKasir::class, 'shift_kasir_id');
    }

    /**
     * Transaksi yang sah dihitung sebagai penjualan: sudah Lunas dan tidak dibatalkan.
     */
    public function scopePenjualanSah($query)
    {
        return $query->where('status_pembayaran', 'Lunas')
            ->where(function ($q) {
                $q->whereNull('status_pesanan')
                  ->orWhere('status_pesanan', '!=', self::STATUS_DIBATALKAN);
            });
    }

    /**
     * Pesanan member yang belum dikonfirmasi pembayarannya oleh member dan punya batas
     * waktu bayar. Timer baru berhenti setelah member menekan "Konfirmasi Pembayaran"
     * (bukti_dikonfirmasi_pada terisi), bukan saat bukti baru diupload.
     * Pesanan lama (batas_bayar_pada NULL) tidak pernah kedaluwarsa otomatis.
     */
    public function menungguBuktiBayar(): bool
    {
        return $this->asal_pesanan === self::ASAL_MEMBER_APP
            && $this->status_pesanan === self::STATUS_DIPROSES
            && $this->status_pembayaran !== 'Lunas'
            && empty($this->bukti_dikonfirmasi_pada)
            && $this->batas_bayar_pada !== null;
    }

    /** Sudah lewat batas waktu bayar dan member belum menekan konfirmasi pembayaran. */
    public function kedaluwarsaBayar(): bool
    {
        return $this->menungguBuktiBayar() && $this->batas_bayar_pada->lte(now());
    }

    /** Sisa waktu (detik) untuk upload + konfirmasi pembayaran; 0 kalau sudah habis / tidak berlaku. */
    public function sisaDetikBayar(): int
    {
        if (! $this->menungguBuktiBayar()) {
            return 0;
        }

        return max(0, $this->batas_bayar_pada->getTimestamp() - now()->getTimestamp());
    }

    /** Alasan pembatalan yang dicatat saat pesanan dibatalkan otomatis karena telat bayar. */
    public static function alasanKedaluwarsaBayar(): string
    {
        return 'Exceeded payment time limit (' . self::BATAS_BAYAR_MENIT . ' minutes), automatically cancelled by the system.';
    }

    /** Pesanan member yang sudah lewat batas waktu bayar tanpa bukti pembayaran. */
    public function scopeTelatBayar($query)
    {
        return $query->where('asal_pesanan', self::ASAL_MEMBER_APP)
            ->where('status_pesanan', self::STATUS_DIPROSES)
            ->where('status_pembayaran', '!=', 'Lunas')
            ->whereNull('bukti_dikonfirmasi_pada')
            ->whereNotNull('batas_bayar_pada')
            ->where('batas_bayar_pada', '<=', now());
    }

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
     * Karakter yang dipakai di kode pesanan. Huruf kapital + angka, tanpa karakter yang
     * gampang tertukar saat dibacakan/diketik (0/O, 1/I/L) supaya kasir tidak salah input.
     */
    public const KARAKTER_KODE_PICKUP = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public const PANJANG_KODE_PICKUP = 6;

    /**
     * Kode pesanan 6 karakter campuran huruf & angka, unik (mis. "A3F9K2"), untuk
     * menyelesaikan pesanan. Dipakai SEMUA pesanan: dari Member App (online) maupun
     * kasir langsung (offline). Selalu mengandung minimal 1 huruf dan 1 angka.
     */
    public static function buatKodePickupUnik(): string
    {
        $karakter = self::KARAKTER_KODE_PICKUP;
        $maks = strlen($karakter) - 1;

        do {
            $kode = '';
            for ($i = 0; $i < self::PANJANG_KODE_PICKUP; $i++) {
                $kode .= $karakter[random_int(0, $maks)];
            }
        } while (
            ! preg_match('/[A-Z]/', $kode)
            || ! preg_match('/[0-9]/', $kode)
            || self::where('kode_pickup', $kode)->exists()
        );

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