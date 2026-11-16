<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Satu shift kerja kasir: modal uang laci awal, uang laci akhir, dan ringkasan
 * pembayaran (tunai / QRIS / debit / Member App) selama shift berlangsung.
 *
 * Catatan: pembayaran Member App disimpan di kolom lama *_lainnya (nama kolom dibiarkan supaya
 * tidak perlu migrasi dan data shift lama tetap terbaca), tapi di kode & tampilan namanya Member App.
 */
class ShiftKasir extends Model
{
    protected $table = 'shift_kasir';

    public const STATUS_BERJALAN = 'berjalan';
    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'user_id',
        'nama_kasir',
        'tanggal',
        'nomor_shift',
        'mulai_pada',
        'selesai_pada',
        'status',
        'modal_awal',
        'uang_akhir',
        'uang_diharapkan',
        'selisih',
        'total_tunai',
        'total_qris',
        'total_debit',
        'total_lainnya',
        'jumlah_tunai',
        'jumlah_qris',
        'jumlah_debit',
        'jumlah_lainnya',
        'jumlah_transaksi',
        'rincian_lainnya',
        'catatan_mulai',
        'catatan_tutup',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'mulai_pada' => 'datetime',
        'selesai_pada' => 'datetime',
        'modal_awal' => 'float',
        'uang_akhir' => 'float',
        'uang_diharapkan' => 'float',
        'selisih' => 'float',
        'total_tunai' => 'float',
        'total_qris' => 'float',
        'total_debit' => 'float',
        'total_lainnya' => 'float',
        'rincian_lainnya' => 'array',
    ];

    /** Variasi tulisan metode pembayaran yang dikelompokkan ke tiga kategori utama. */
    private const KELOMPOK_METODE = [
        'tunai' => ['tunai', 'cash'],
        'qris' => ['qris'],
        'debit' => ['debit', 'kartu', 'kartu debit', 'edc'],
    ];

    /** Metode pembayaran pesanan Member App -> label yang mudah dibaca di rekap. */
    private const LABEL_METODE_MEMBER_APP = [
        'transfer/qris' => 'QRIS',
        'transfer' => 'QRIS',
        'qris' => 'QRIS',
        'voucher' => 'Voucher (gratis)',
        'reward stamp' => 'Reward Stamp (gratis)',
    ];

    // ------------------------------------------------------------------ relasi

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'shift_kasir_id');
    }

    // ------------------------------------------------------------------- scope

    public function scopeBerjalan($query)
    {
        return $query->where('status', self::STATUS_BERJALAN);
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    // ----------------------------------------------------------------- helper

    public function sedangBerjalan(): bool
    {
        return $this->status === self::STATUS_BERJALAN;
    }

    public function labelShift(): string
    {
        return 'Shift ' . $this->nomor_shift;
    }

    /** Shift yang sedang berjalan milik user ini (null kalau belum buka shift). */
    public static function aktifUntuk(?int $userId): ?self
    {
        if (! $userId) {
            return null;
        }

        return self::berjalan()->where('user_id', $userId)->latest('mulai_pada')->first();
    }

    /** Shift yang sedang berjalan milik orang LAIN (bukan user ini), null kalau tidak ada. */
    public static function berjalanMilikLain(?int $userId): ?self
    {
        return self::berjalan()
            ->when($userId, fn ($q) => $q->where('user_id', '!=', $userId))
            ->latest('mulai_pada')
            ->first();
    }

    /** Shift berjalan siapa pun pemiliknya. Karena hanya boleh satu, ini "shift laci" saat ini. */
    public static function berjalanSaatIni(): ?self
    {
        return self::berjalan()->latest('mulai_pada')->first();
    }

    /** ID shift yang sedang berjalan (siapa pun pemiliknya), untuk pembayaran online yang dikonfirmasi. */
    public static function idBerjalanSaatIni(): ?int
    {
        return self::berjalanSaatIni()?->id;
    }

    /** ID shift berjalan milik user yang sedang login, dipakai untuk menandai transaksi. */
    public static function idAktifSaatIni(): ?int
    {
        return self::aktifUntuk(auth()->id())?->id;
    }

    /** Shift terakhir yang sudah ditutup (siapa pun kasirnya), untuk saran modal laci awal. */
    public static function terakhirSelesai(): ?self
    {
        return self::selesai()->whereNotNull('uang_akhir')->latest('selesai_pada')->first();
    }

    /**
     * Buka shift baru. Nomor shift = urutan shift pada tanggal itu (Shift 1, Shift 2, ...).
     * Hanya SATU shift yang boleh berjalan pada satu waktu (satu laci): kalau masih ada shift
     * berjalan milik siapa pun, shift baru ditolak sampai shift itu ditutup.
     * Dikunci di dalam transaksi supaya dua orang yang buka bersamaan tidak sama-sama lolos
     * dan tidak dapat nomor shift yang sama.
     */
    public static function mulai(User $user, float $modalAwal, ?string $catatan = null): self
    {
        return DB::transaction(function () use ($user, $modalAwal, $catatan) {
            // Kunci satu baris yang sama untuk semua pemanggil, jadi buka shift diproses satu per satu.
            User::query()->orderBy('id')->lockForUpdate()->first();

            $berjalan = self::berjalanSaatIni();

            if ($berjalan) {
                if ((int) $berjalan->user_id === (int) $user->id) {
                    throw new \RuntimeException('Anda masih punya shift yang berjalan. Tutup dulu sebelum membuka shift baru.');
                }

                throw new \RuntimeException(
                    $berjalan->labelShift() . ' milik ' . $berjalan->nama_kasir . ' (mulai '
                    . $berjalan->mulai_pada->translatedFormat('d M H:i') . ') masih berjalan. '
                    . 'Hanya satu shift yang boleh berjalan. Minta ' . $berjalan->nama_kasir
                    . ' menutupnya dulu, atau admin bisa menutup paksa lewat Rekap Shift.'
                );
            }

            $tanggal = now()->toDateString();
            $nomor = ((int) self::whereDate('tanggal', $tanggal)->lockForUpdate()->max('nomor_shift')) + 1;

            return self::create([
                'user_id' => $user->id,
                'nama_kasir' => $user->name,
                'tanggal' => $tanggal,
                'nomor_shift' => $nomor,
                'mulai_pada' => now(),
                'status' => self::STATUS_BERJALAN,
                'modal_awal' => $modalAwal,
                'catatan_mulai' => $catatan,
            ]);
        });
    }

    /**
     * Hitung ringkasan pembayaran shift ini langsung dari transaksi.
     * Hanya transaksi Lunas & tidak dibatalkan. Nominal = total tagihan (bukan uang yang
     * diterima), jadi kembalian tidak ikut terhitung sebagai uang masuk.
     */
    public function hitungRingkasan(): array
    {
        $baris = Transaksi::penjualanSah()
            ->where('shift_kasir_id', $this->id)
            ->selectRaw('LOWER(TRIM(metode_pembayaran)) as metode, asal_pesanan, COUNT(*) as jumlah, COALESCE(SUM(total_bayar), 0) as nominal')
            ->groupByRaw('LOWER(TRIM(metode_pembayaran)), asal_pesanan')
            ->get();

        $ringkasan = [
            'tunai' => ['jumlah' => 0, 'nominal' => 0.0],
            'qris' => ['jumlah' => 0, 'nominal' => 0.0],
            'debit' => ['jumlah' => 0, 'nominal' => 0.0],
            'member_app' => ['jumlah' => 0, 'nominal' => 0.0, 'rincian' => []],
        ];

        foreach ($baris as $b) {
            $metode = (string) $b->metode;
            $dariMemberApp = $b->asal_pesanan === Transaksi::ASAL_MEMBER_APP;
            $kategori = 'member_app';

            // Pesanan Member App selalu masuk kolom Member App, apa pun metode yang tertulis.
            // Transaksi kasir dikelompokkan menurut metodenya.
            if (! $dariMemberApp) {
                foreach (self::KELOMPOK_METODE as $nama => $varian) {
                    if (in_array($metode, $varian, true)) {
                        $kategori = $nama;
                        break;
                    }
                }
            }

            $ringkasan[$kategori]['jumlah'] += (int) $b->jumlah;
            $ringkasan[$kategori]['nominal'] += (float) $b->nominal;

            if ($kategori === 'member_app') {
                $ringkasan['member_app']['rincian'][] = [
                    'metode' => self::labelMetode($metode),
                    'jumlah' => (int) $b->jumlah,
                    'nominal' => (float) $b->nominal,
                ];
            }
        }

        $ringkasan['member_app']['rincian'] = self::gabungRincian($ringkasan['member_app']['rincian']);

        return $this->lengkapiRingkasan($ringkasan);
    }

    /** Label metode untuk rincian Member App (QRIS, Voucher (gratis), Reward Stamp (gratis), ...). */
    private static function labelMetode(string $metode): string
    {
        $kunci = strtolower(trim($metode));

        if ($kunci === '') {
            return 'Lainnya';
        }

        return self::LABEL_METODE_MEMBER_APP[$kunci] ?? ucwords($kunci);
    }

    /** Gabungkan baris rincian berlabel sama dan urutkan dari nominal terbesar. */
    private static function gabungRincian(array $rincian): array
    {
        $hasil = [];

        foreach ($rincian as $r) {
            $label = self::labelMetode((string) ($r['metode'] ?? ''));
            $hasil[$label] ??= ['metode' => $label, 'jumlah' => 0, 'nominal' => 0.0];
            $hasil[$label]['jumlah'] += (int) ($r['jumlah'] ?? 0);
            $hasil[$label]['nominal'] += (float) ($r['nominal'] ?? 0);
        }

        $hasil = array_values($hasil);
        usort($hasil, fn ($a, $b) => $b['nominal'] <=> $a['nominal']);

        return $hasil;
    }

    /**
     * Ringkasan untuk ditampilkan: shift selesai memakai angka yang sudah disimpan
     * (tidak berubah lagi), shift berjalan dihitung langsung.
     */
    public function ringkasan(): array
    {
        if ($this->sedangBerjalan()) {
            return $this->hitungRingkasan();
        }

        return $this->lengkapiRingkasan([
            'tunai' => ['jumlah' => (int) $this->jumlah_tunai, 'nominal' => (float) $this->total_tunai],
            'qris' => ['jumlah' => (int) $this->jumlah_qris, 'nominal' => (float) $this->total_qris],
            'debit' => ['jumlah' => (int) $this->jumlah_debit, 'nominal' => (float) $this->total_debit],
            'member_app' => [
                'jumlah' => (int) $this->jumlah_lainnya,
                'nominal' => (float) $this->total_lainnya,
                'rincian' => self::gabungRincian($this->rincian_lainnya ?? []),
            ],
        ]);
    }

    private function lengkapiRingkasan(array $ringkasan): array
    {
        $ringkasan['jumlah_transaksi'] = $ringkasan['tunai']['jumlah'] + $ringkasan['qris']['jumlah']
            + $ringkasan['debit']['jumlah'] + $ringkasan['member_app']['jumlah'];
        $ringkasan['total_penjualan'] = $ringkasan['tunai']['nominal'] + $ringkasan['qris']['nominal']
            + $ringkasan['debit']['nominal'] + $ringkasan['member_app']['nominal'];
        $ringkasan['modal_awal'] = (float) $this->modal_awal;
        // Uang yang seharusnya ada di laci = modal awal + penjualan tunai (QRIS, debit, Member App tidak masuk laci).
        $ringkasan['uang_diharapkan'] = $ringkasan['modal_awal'] + $ringkasan['tunai']['nominal'];

        return $ringkasan;
    }

    /**
     * Tutup shift: simpan uang laci akhir, hitung selisih dan bekukan ringkasan pembayaran.
     */
    public function tutup(float $uangAkhir, ?string $catatan = null): self
    {
        return $this->selesaikan($uangAkhir, $catatan);
    }

    /**
     * Tutup paksa oleh admin (kasirnya lupa/tidak bisa menutup). Uang laci akhir boleh dikosongkan
     * kalau tidak ada yang menghitungnya; selisih jadi tidak diketahui (null). Alasan wajib dan
     * ikut tersimpan di catatan akhir shift supaya terlihat di rekap.
     */
    public function tutupPaksa(User $oleh, ?float $uangAkhir, string $alasan): self
    {
        $catatan = 'Ditutup paksa oleh ' . $oleh->name . ': ' . trim($alasan);

        return $this->selesaikan($uangAkhir, $catatan);
    }

    private function selesaikan(?float $uangAkhir, ?string $catatan): self
    {
        $r = $this->hitungRingkasan();

        $this->update([
            'status' => self::STATUS_SELESAI,
            'selesai_pada' => now(),
            'uang_akhir' => $uangAkhir,
            'uang_diharapkan' => $r['uang_diharapkan'],
            'selisih' => $uangAkhir === null ? null : round($uangAkhir - $r['uang_diharapkan'], 2),
            'total_tunai' => $r['tunai']['nominal'],
            'total_qris' => $r['qris']['nominal'],
            'total_debit' => $r['debit']['nominal'],
            'total_lainnya' => $r['member_app']['nominal'],
            'jumlah_tunai' => $r['tunai']['jumlah'],
            'jumlah_qris' => $r['qris']['jumlah'],
            'jumlah_debit' => $r['debit']['jumlah'],
            'jumlah_lainnya' => $r['member_app']['jumlah'],
            'jumlah_transaksi' => $r['jumlah_transaksi'],
            'rincian_lainnya' => $r['member_app']['rincian'] ?: null,
            'catatan_tutup' => $catatan,
        ]);

        return $this;
    }

    /** Teks status selisih untuk tampilan: Sesuai / Lebih / Kurang. */
    public function labelSelisih(): ?string
    {
        if ($this->selisih === null) {
            return null;
        }

        if (abs($this->selisih) < 0.5) {
            return 'Sesuai';
        }

        return $this->selisih > 0 ? 'Lebih' : 'Kurang';
    }
}