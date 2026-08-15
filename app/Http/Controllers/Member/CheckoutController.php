<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Models\DetailTransaksi;
use App\Models\Member;
use App\Models\PenukaranPoin;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Support\MemberCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\PengaturanPajak;

class CheckoutController extends Controller
{
    private const NAMA_TOKO = 'Saisho Cake & Bakery';
    private const ALAMAT_TOKO = 'Jl. Kamboja No. 1 Tuparev, Cirebon';
    private const SESSION_VOUCHER_KEY = 'member_checkout_voucher_id';

    public function index()
    {
        $member = Auth::guard('member')->user();

        if (MemberCart::isEmpty()) {
            return redirect()->route('member.menu')->with('error', 'Keranjang kamu masih kosong.');
        }

        $items = MemberCart::items();
        $subtotal = MemberCart::subtotal();

        $voucherAktif = PenukaranPoin::with('reward.produks')
            ->where('member_id', $member->id)
            ->whereHas('reward', fn ($q) => $q->where('jenis', 'voucher'))
            ->aktifDigunakan()
            ->latest()
            ->get();

        $voucherId = Session::get(self::SESSION_VOUCHER_KEY);
        $voucherDipilih = $voucherId ? $voucherAktif->firstWhere('id', $voucherId) : null;

        $diskon = $voucherDipilih ? $this->hitungDiskonVoucher($voucherDipilih, $items, $subtotal) : 0;
        $pajak = $this->hitungPajak($subtotal, $diskon);
        $totalBayar = max(0, $subtotal - $diskon + $pajak);

        $voucherTerbaik = null;
        if (! $voucherDipilih) {
            $kandidatTerbaik = $voucherAktif
                ->map(fn ($v) => [
                    'voucher' => $v,
                    'diskon' => $this->hitungDiskonVoucher($v, $items, $subtotal),
                ])
                ->filter(fn ($x) => $x['diskon'] > 0)
                ->sortByDesc('diskon')
                ->first();

            $voucherTerbaik = $kandidatTerbaik ?: null;
        }

        $pajakInfo = $this->getPajakInfo();

        return view('member.checkout.index', [
            'member' => $member,
            'items' => $items,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'pajak' => $pajak,
            'pajakPersen' => $pajakInfo['persen'],
            'totalBayar' => $totalBayar,
            'voucherAktif' => $voucherAktif,
            'voucherDipilih' => $voucherDipilih,
            'voucherTerbaik' => $voucherTerbaik,
            'storeNama' => self::NAMA_TOKO,
            'storeAlamat' => self::ALAMAT_TOKO,
            'poinPotensial' => $this->estimasiPoin($member, $totalBayar),
        ]);
    }

    public function pilihVoucher(Request $request)
    {
        $validated = $request->validate([
            'voucher_id' => 'nullable|integer|exists:penukaran_poin,id',
        ]);

        if (empty($validated['voucher_id'])) {
            Session::forget(self::SESSION_VOUCHER_KEY);
            return back();
        }

        $member = Auth::guard('member')->user();
        $milikMember = PenukaranPoin::where('id', $validated['voucher_id'])
            ->where('member_id', $member->id)
            ->exists();

        if (! $milikMember) {
            return back()->with('error', 'Voucher tidak ditemukan.');
        }

        Session::put(self::SESSION_VOUCHER_KEY, (int) $validated['voucher_id']);

        return back();
    }

    public function store(Request $request)
    {
        $member = Auth::guard('member')->user();

        if (MemberCart::isEmpty()) {
            return redirect()->route('member.menu')->with('error', 'Keranjang kamu masih kosong.');
        }

        $request->validate([
            'catatan' => 'nullable|string|max:255',
        ]);

        $items = MemberCart::items();
        $subtotal = MemberCart::subtotal();
        $voucherId = Session::get(self::SESSION_VOUCHER_KEY);

        DB::beginTransaction();
        try {
            $voucher = null;
            $diskon = 0;

            if ($voucherId) {
                $voucher = PenukaranPoin::with('reward.produks')
                    ->where('id', $voucherId)
                    ->where('member_id', $member->id)
                    ->lockForUpdate()
                    ->first();

                if (! $voucher || $voucher->status !== 'menunggu_diambil' || $voucher->sudahExpired()) {
                    throw new \Exception('Voucher yang dipilih sudah tidak berlaku, silakan pilih ulang.');
                }

                $diskon = $this->hitungDiskonVoucher($voucher, $items, $subtotal);
            }

            $pajak = $this->hitungPajak($subtotal, $diskon);
            $totalBayar = max(0, $subtotal - $diskon + $pajak);

            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_pembeli' => $member->nama,
                'member_id' => $member->id,
                'voucher_id' => $voucher?->id,
                'subtotal' => $subtotal,
                'pajak' => $pajak,
                'diskon' => $diskon,
                'diskon_persen' => 0,
                'diskon_label' => $voucher ? ($voucher->reward->nama ?? 'Voucher') : null,
                'total_bayar' => $totalBayar,
                'jumlah_diterima' => $totalBayar,
                'kembalian' => 0,
                'metode_pembayaran' => 'Transfer/QRIS',
                'status_pembayaran' => 'Menunggu Pembayaran',
                'asal_pesanan' => Transaksi::ASAL_MEMBER_APP,
                'status_pesanan' => Transaksi::STATUS_DIPROSES,
                // Kode pickup baru digenerate nanti waktu kasir menandai
                // pesanan "Ready for Pickup" (lihat
                // PesananPickupController::tandaiSiap()), bukan di sini.
                'catatan_pesanan' => $request->input('catatan'),
            ]);

            if ($voucher) {
                $voucher->update([
                    'status' => 'selesai',
                    'diambil_pada' => now(),
                    'transaksi_id' => $transaksi->id,
                ]);
            }

            foreach ($items as $item) {
                if (! empty($item['bundling_id'])) {
                    $this->prosesItemBundlingCheckout($transaksi, $item);
                    continue;
                }

                $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);
                $pecahan = $this->pecahJumlahProduksi($produk, (int) $item['jumlah']);

                $catatanItem = trim(collect([
                    $item['ukuran'] ?? null,
                    $item['catatan'] ?? null,
                ])->filter()->implode(' — '));

                foreach ($pecahan as $bagian) {
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $produk->id,
                        'nama_produk' => $produk->nama_produk,
                        'harga_satuan' => $item['harga_satuan'],
                        'jumlah' => $bagian['jumlah'],
                        'catatan' => $catatanItem !== '' ? $catatanItem : null,
                        'subtotal' => $item['harga_satuan'] * $bagian['jumlah'],
                        'butuh_produksi' => $bagian['butuh_produksi'],
                    ]);
                }
            }

            DB::commit();

            activity()
                ->causedBy($member)
                ->performedOn($transaksi)
                ->withProperties([
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $kodeTransaksi,
                ])
                ->log('membuat pesanan pick up dari member app');

            MemberCart::kosongkan();
            Session::forget(self::SESSION_VOUCHER_KEY);

            // Kabari dashboard POS real-time supaya pesanan baru langsung
            // muncul di halaman "Pesanan" kasir tanpa perlu refresh manual.
            event(new \App\Events\PesananPickupUpdated($transaksi, 'baru'));

            return redirect()
                ->route('member.pesanan.show', $transaksi)
                ->with('success', 'Pesanan berhasil dibuat! Kami akan proses pesananmu segera.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checkout member app: ' . $e->getMessage());

            return back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }
    private function prosesItemBundlingCheckout(Transaksi $transaksi, array $item): void
    {
        $bundling = Bundling::with('items.produk')->findOrFail($item['bundling_id']);
        $jumlahBundling = (int) $item['jumlah'];

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => null,
            'bundling_id' => $bundling->id,
            'nama_produk' => $bundling->nama_bundling,
            'harga_satuan' => $item['harga_satuan'],
            'jumlah' => $jumlahBundling,
            'catatan' => $item['catatan'] ?? null,
            'subtotal' => $item['harga_satuan'] * $jumlahBundling,
            'butuh_produksi' => false,
        ]);

        foreach ($bundling->items as $bundlingItem) {
            $produk = $bundlingItem->produk;
            if (! $produk) {
                continue;
            }

            $totalDibutuhkan = $bundlingItem->qty * $jumlahBundling;
            $pecahan = $this->pecahJumlahProduksi($produk, $totalDibutuhkan);

            foreach ($pecahan as $bagian) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'bundling_id' => $bundling->id,
                    'nama_produk' => $produk->nama_produk,
                    'harga_satuan' => 0,
                    'jumlah' => $bagian['jumlah'],
                    'catatan' => null,
                    'subtotal' => 0,
                    'butuh_produksi' => $bagian['butuh_produksi'],
                ]);
            }
        }
    }
    private function pecahJumlahProduksi(Produk $produk, int $jumlah): array
    {
        if ($produk->status_ketersediaan === 'perlu_diproduksi') {
            return [['jumlah' => $jumlah, 'butuh_produksi' => true]];
        }

        $stokTersedia = max(0, (int) floor((float) $produk->stok));

        if ($stokTersedia >= $jumlah) {
            return [['jumlah' => $jumlah, 'butuh_produksi' => false]];
        }

        if ($stokTersedia <= 0) {
            return [['jumlah' => $jumlah, 'butuh_produksi' => true]];
        }

        return [
            ['jumlah' => $stokTersedia, 'butuh_produksi' => false],
            ['jumlah' => $jumlah - $stokTersedia, 'butuh_produksi' => true],
        ];
    }

    /**
     * Hitung nominal diskon dari voucher — meniru aturan yang sama dipakai
     * di kasir (VoucherKasirController): nominal langsung, atau persentase
     * dari basis harga (dibatasi ke produk tertentu kalau voucher-nya
     * scoped ke produk, dan dipotong maksimal_potongan bila ada).
     */
    private function hitungDiskonVoucher(PenukaranPoin $voucher, $items, float $subtotal): float
    {
        $reward = $voucher->reward;
        if (! $reward) {
            return 0;
        }

        if ((float) ($reward->min_pembelian ?? 0) > $subtotal) {
            return 0;
        }

        $produkIds = $reward->produks->pluck('id')->values()->all();

        $basis = $subtotal;
        if (! empty($produkIds)) {
            $basis = collect($items)
                ->filter(fn ($item) => in_array($item['produk_id'] ?? null, $produkIds, true))
                ->sum(fn ($item) => $item['harga_satuan'] * $item['jumlah']);
        }

        if ($reward->jenis_potongan === 'nominal') {
            $diskon = (float) $reward->nominal_potongan;
        } else {
            $diskon = $basis * ((float) $reward->persentase / 100);
            if (! is_null($reward->maksimal_potongan)) {
                $diskon = min($diskon, (float) $reward->maksimal_potongan);
            }
        }

        return min($diskon, $basis);
    }

    private function getPajakInfo(): array
    {
        $pajak = PengaturanPajak::getAktif();
        if (!$pajak->status_aktif) {
            return ['aktif' => false, 'persen' => 0];
        }
        return ['aktif' => true, 'persen' => (float) $pajak->persentase];
    }

    private function hitungPajak(float $subtotal, float $diskon): float
    {
        $info = $this->getPajakInfo();
        if (!$info['aktif']) {
            return 0;
        }

        $basis = max(0, $subtotal - $diskon);

        return round($basis * ($info['persen'] / 100));
    }

    private function estimasiPoin(Member $member, float $totalBayar): int
    {
        $pengaturan = \App\Models\PengaturanMember::where('status_aktif', true)->first();
        if (! $pengaturan || $pengaturan->minimal_belanja <= 0) {
            return 0;
        }

        return (int) (floor($totalBayar / $pengaturan->minimal_belanja) * $pengaturan->poin);
    }
}