<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Satu shift kerja kasir: modal uang laci awal, uang laci akhir, dan ringkasan
 * pembayaran (tunai / QRIS / debit / lainnya) selama shift berlangsung.
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
     * Dikunci di dalam transaksi supaya dua kasir yang buka bersamaan tidak dapat nomor sama
     * dan satu kasir tidak bisa punya dua shift berjalan.
     */
    public static function mulai(User $user, float $modalAwal, ?string $catatan = null): self
    {
        return DB::transaction(function () use ($user, $modalAwal, $catatan) {
            // Kunci baris user untuk menyerialkan permintaan buka shift dari kasir yang sama.
            User::whereKey($user->id)->lockForUpdate()->first();

            if (self::aktifUntuk($user->id)) {
                throw new \RuntimeException('Anda masih punya shift yang berjalan. Tutup dulu sebelum membuka shift baru.');
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
            ->selectRaw('LOWER(TRIM(metode_pembayaran)) as metode, COUNT(*) as jumlah, COALESCE(SUM(total_bayar), 0) as nominal')
            ->groupByRaw('LOWER(TRIM(metode_pembayaran))')
            ->get();

        $ringkasan = [
            'tunai' => ['jumlah' => 0, 'nominal' => 0.0],
            'qris' => ['jumlah' => 0, 'nominal' => 0.0],
            'debit' => ['jumlah' => 0, 'nominal' => 0.0],
            'lainnya' => ['jumlah' => 0, 'nominal' => 0.0, 'rincian' => []],
        ];

        foreach ($baris as $b) {
            $metode = (string) $b->metode;
            $kategori = 'lainnya';

            foreach (self::KELOMPOK_METODE as $nama => $varian) {
                if (in_array($metode, $varian, true)) {
                    $kategori = $nama;
                    break;
                }
            }

            $ringkasan[$kategori]['jumlah'] += (int) $b->jumlah;
            $ringkasan[$kategori]['nominal'] += (float) $b->nominal;

            if ($kategori === 'lainnya') {
                $ringkasan['lainnya']['rincian'][] = [
                    'metode' => $metode !== '' ? ucwords($metode) : 'Lainnya',
                    'jumlah' => (int) $b->jumlah,
                    'nominal' => (float) $b->nominal,
                ];
            }
        }

        return $this->lengkapiRingkasan($ringkasan);
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
            'lainnya' => [
                'jumlah' => (int) $this->jumlah_lainnya,
                'nominal' => (float) $this->total_lainnya,
                'rincian' => $this->rincian_lainnya ?? [],
            ],
        ]);
    }

    private function lengkapiRingkasan(array $ringkasan): array
    {
        $ringkasan['jumlah_transaksi'] = $ringkasan['tunai']['jumlah'] + $ringkasan['qris']['jumlah']
            + $ringkasan['debit']['jumlah'] + $ringkasan['lainnya']['jumlah'];
        $ringkasan['total_penjualan'] = $ringkasan['tunai']['nominal'] + $ringkasan['qris']['nominal']
            + $ringkasan['debit']['nominal'] + $ringkasan['lainnya']['nominal'];
        $ringkasan['modal_awal'] = (float) $this->modal_awal;
        // Uang yang seharusnya ada di laci = modal awal + penjualan tunai.
        $ringkasan['uang_diharapkan'] = $ringkasan['modal_awal'] + $ringkasan['tunai']['nominal'];

        return $ringkasan;
    }

    /**
     * Tutup shift: simpan uang laci akhir, hitung selisih dan bekukan ringkasan pembayaran.
     */
    public function tutup(float $uangAkhir, ?string $catatan = null): self
    {
        $r = $this->hitungRingkasan();

        $this->update([
            'status' => self::STATUS_SELESAI,
            'selesai_pada' => now(),
            'uang_akhir' => $uangAkhir,
            'uang_diharapkan' => $r['uang_diharapkan'],
            'selisih' => round($uangAkhir - $r['uang_diharapkan'], 2),
            'total_tunai' => $r['tunai']['nominal'],
            'total_qris' => $r['qris']['nominal'],
            'total_debit' => $r['debit']['nominal'],
            'total_lainnya' => $r['lainnya']['nominal'],
            'jumlah_tunai' => $r['tunai']['jumlah'],
            'jumlah_qris' => $r['qris']['jumlah'],
            'jumlah_debit' => $r['debit']['jumlah'],
            'jumlah_lainnya' => $r['lainnya']['jumlah'],
            'jumlah_transaksi' => $r['jumlah_transaksi'],
            'rincian_lainnya' => $r['lainnya']['rincian'] ?: null,
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