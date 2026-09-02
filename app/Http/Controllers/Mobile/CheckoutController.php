<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Models\PromoProdukEvent;
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

use App\Support\ActivityLogHelper;

class CheckoutController extends Controller
{
    private const NAMA_TOKO = 'Saisho Cake & Bakery';
    private const ALAMAT_TOKO = 'Jl. Kamboja No. 1 Tuparev, Cirebon';
    private const SESSION_VOUCHER_KEY = 'member_checkout_voucher_id';

    public function index()
    {
        try {
            $member = Auth::guard('member')->user();

            if (MemberCart::isEmpty()) {
                return redirect()->route('member.menu')->with('error', 'Keranjang kamu masih kosong.');
            }

            MemberCart::terapkanMinimalPembelian();

            $items = MemberCart::items();
            $subtotalGross = MemberCart::subtotalGross();
            $diskonProduk = MemberCart::totalDiskonProduk();
            $subtotalNet = MemberCart::subtotal();

            $voucherAktif = PenukaranPoin::with('reward.produks')
                ->where('member_id', $member->id)
                ->whereHas('reward', fn ($q) => $q->where('jenis', 'voucher'))
                ->aktifDigunakan()
                ->latest()
                ->get();

            $voucherId = Session::get(self::SESSION_VOUCHER_KEY);
            $voucherDipilih = $voucherId ? $voucherAktif->firstWhere('id', $voucherId) : null;

            $diskonVoucher = $voucherDipilih ? $this->hitungDiskonVoucher($voucherDipilih, $items, $subtotalNet) : 0;
            $diskonTotal = $diskonProduk + $diskonVoucher;
            $pajak = $this->hitungPajak($subtotalGross, $diskonTotal);
            $totalBayar = max(0, $subtotalGross - $diskonTotal + $pajak);

            $voucherTerbaik = null;
            if (! $voucherDipilih) {
                $kandidatTerbaik = $voucherAktif
                    ->map(fn ($v) => [
                        'voucher' => $v,
                        'diskon' => $this->hitungDiskonVoucher($v, $items, $subtotalNet),
                    ])
                    ->filter(fn ($x) => $x['diskon'] > 0)
                    ->sortByDesc('diskon')
                    ->first();

                $voucherTerbaik = $kandidatTerbaik ?: null;
            }

            $pajakInfo = $this->getPajakInfo();

            // Hitung rincian diskon promo per nama promo (sama formatnya seperti POS & store())
            $rincianDiskonProduk = [];
            $promoGroupsIdx = [];
            foreach ($items as $item) {
                $diskonItemSatuan = (float) ($item['diskon_produk_satuan'] ?? 0);
                if ($diskonItemSatuan > 0) {
                    $namaPromo   = $item['nama_promo'] ?? 'Promo';
                    $jenisPromo  = $item['jenis_promo'] ?? null;
                    $persenPromo = $item['persentase_promo'] ?? null;

                    $labelPromo = $namaPromo;
                    if ($jenisPromo === 'persentase' && $persenPromo) {
                        $persenFormatted = rtrim(rtrim(number_format((float) $persenPromo, 2, ',', '.'), '0'), ',');
                        $labelPromo .= ' (' . $persenFormatted . '%)';
                    }

                    if (!isset($promoGroupsIdx[$labelPromo])) {
                        $promoGroupsIdx[$labelPromo] = 0;
                    }
                    $promoGroupsIdx[$labelPromo] += $diskonItemSatuan * $item['jumlah'];
                }
            }
            foreach ($promoGroupsIdx as $labelPromo => $nilai) {
                $rincianDiskonProduk[] = [
                    'nama' => $labelPromo,
                    'nilai' => (float) $nilai,
                ];
            }

            return view('mobile.checkout.index', [
                'member' => $member,
                'items' => $items,
                'subtotal' => $subtotalGross,
                'subtotalNet' => $subtotalNet,
                'diskonProduk' => $diskonProduk,
                'rincianDiskonProduk' => $rincianDiskonProduk,
                'diskonVoucher' => $diskonVoucher,
                'diskon' => $diskonTotal,
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function pilihVoucher(Request $request)
    {
        try {
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();

            if (MemberCart::isEmpty()) {
                return redirect()->route('member.menu')->with('error', 'Keranjang kamu masih kosong.');
            }

            $request->validate([
                'catatan' => 'nullable|string|max:255',
            ]);

            MemberCart::terapkanMinimalPembelian();

            $items = MemberCart::items();
            $subtotalGross = MemberCart::subtotalGross();
            $diskonProduk = MemberCart::totalDiskonProduk();
            $subtotalNet = MemberCart::subtotal();
            $voucherId = Session::get(self::SESSION_VOUCHER_KEY);

            DB::beginTransaction();
            try {
                $voucher = null;
                $diskonVoucher = 0;

                if ($voucherId) {
                    $voucher = PenukaranPoin::with('reward.produks')
                        ->where('id', $voucherId)
                        ->where('member_id', $member->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $voucher || $voucher->status !== 'menunggu_diambil' || $voucher->sudahExpired()) {
                        throw new \Exception('Voucher yang dipilih sudah tidak berlaku, silakan pilih ulang.');
                    }

                    $diskonVoucher = $this->hitungDiskonVoucher($voucher, $items, $subtotalNet);
                }

                $diskonTotal = $diskonProduk + $diskonVoucher;
                $pajak = $this->hitungPajak($subtotalGross, $diskonTotal);
                $totalBayar = max(0, $subtotalGross - $diskonTotal + $pajak);

                $rincianDiskon = [];
                $promoGroups = [];
                foreach ($items as $item) {
                    $diskonItemSatuan = (float) ($item['diskon_produk_satuan'] ?? 0);
                    if ($diskonItemSatuan > 0) {
                        $namaPromo   = $item['nama_promo'] ?? 'Promo';
                        $jenisPromo  = $item['jenis_promo'] ?? null;
                        $persenPromo = $item['persentase_promo'] ?? null;

                        // Buat label persis seperti POS: "Nama Promo (X%)" untuk persentase
                        $labelPromo = $namaPromo;
                        if ($jenisPromo === 'persentase' && $persenPromo) {
                            $persenFormatted = rtrim(rtrim(number_format((float) $persenPromo, 2, ',', '.'), '0'), ',');
                            $labelPromo .= ' (' . $persenFormatted . '%)';
                        }

                        if (!isset($promoGroups[$labelPromo])) {
                            $promoGroups[$labelPromo] = 0;
                        }
                        $promoGroups[$labelPromo] += $diskonItemSatuan * $item['jumlah'];
                    }
                }
                foreach ($promoGroups as $labelPromo => $nilai) {
                    $rincianDiskon[] = [
                        'nama' => $labelPromo,
                        'nilai' => (float) $nilai,
                    ];
                }
                if ($voucher && $diskonVoucher > 0) {
                    $rincianDiskon[] = [
                        'nama' => $voucher->reward->nama ?? 'Voucher',
                        'nilai' => (float) $diskonVoucher,
                    ];
                }

                $diskonLabel = !empty($rincianDiskon) ? json_encode($rincianDiskon) : null;

                $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

                $transaksi = Transaksi::create([
                    'kode_transaksi' => $kodeTransaksi,
                    'nama_pembeli' => $member->nama,
                    'member_id' => $member->id,
                    'voucher_id' => $voucher?->id,
                    'subtotal' => $subtotalGross,
                    'pajak' => $pajak,
                    'diskon' => $diskonTotal,
                    'diskon_persen' => 0,
                    'diskon_label' => $diskonLabel,
                    'total_bayar' => $totalBayar,
                    'jumlah_diterima' => $totalBayar,
                    'kembalian' => 0,
                    'metode_pembayaran' => 'Transfer/QRIS',
                    'status_pembayaran' => 'Menunggu Pembayaran',
                    'asal_pesanan' => Transaksi::ASAL_MEMBER_APP,
                    'status_pesanan' => Transaksi::STATUS_DIPROSES,
                    'catatan_pesanan' => $request->input('catatan'),
                ]);

                if ($voucher) {
                    $voucher->update([
                        'status' => 'selesai',
                        'diambil_pada' => now(),
                        'transaksi_id' => $transaksi->id,
                    ]);
                }

                $stokTerpakai = [];

                foreach ($items as $item) {
                    if (! empty($item['bundling_id'])) {
                        $this->prosesItemBundlingCheckout($transaksi, $item, $stokTerpakai);
                        continue;
                    }

                    if (! empty($item['promo_event_id'])) {
                        $this->prosesItemPromoEventCheckout($transaksi, $item, $stokTerpakai);
                        continue;
                    }

                    $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);
                    $catatanItem = $item['catatan'] ?? null;
                    $ukuranItem = $item['ukuran'] ?? null;
                    $pecahan = $this->pecahJumlahProduksi($produk, (int) $item['jumlah'], $ukuranItem, $stokTerpakai);

                    $hargaSatuanDetail = (float) ($item['harga_asal'] ?? $item['harga_satuan']);

                    foreach ($pecahan as $bagian) {
                        DetailTransaksi::create([
                            'transaksi_id' => $transaksi->id,
                            'produk_id' => $produk->id,
                            'nama_produk' => $produk->nama_produk,
                            'ukuran' => $ukuranItem,
                            'harga_satuan' => $hargaSatuanDetail,
                            'jumlah' => $bagian['jumlah'],
                            'catatan' => $catatanItem !== '' ? $catatanItem : null,
                            'subtotal' => $hargaSatuanDetail * $bagian['jumlah'],
                            'butuh_produksi' => $bagian['butuh_produksi'],
                        ]);
                    }
                }

                DB::commit();

                try {
                    \App\Models\AppNotification::create([
                        'type' => 'Pesanan Pickup Baru',
                        'title' => 'Pesanan Pickup Baru #' . $kodeTransaksi,
                        'desc' => 'Pesanan pickup baru atas nama ' . $member->nama . ' (Rp ' . number_format($totalBayar, 0, ',', '.') . ').',
                        'icon_type' => 'success',
                        'url' => route('pesanan-pickup.indeks'),
                        'is_read' => false,
                    ]);
                } catch (\Throwable $e) {}

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

                \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'baru')));

                return redirect()
                    ->route('member.pesanan.show', $transaksi)
                    ->with('success', 'Pesanan berhasil dibuat! Kami akan proses pesananmu segera.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error checkout member app: ' . $e->getMessage());

                return ActivityLogHelper::handleException($e, 'Error checkout member app: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
    private function prosesItemBundlingCheckout(Transaksi $transaksi, array $item, array &$stokTerpakai): void
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

        $pilihanKhusus = $item['pilihan_khusus'] ?? [];
        $mapCatatanPilihan = [];
        $mapQtyPilihan = [];
        if (is_array($pilihanKhusus)) {
            foreach ($pilihanKhusus as $p) {
                if (isset($p['produk_id'])) {
                    if (!empty($p['catatan'])) {
                        $mapCatatanPilihan[$p['produk_id']] = $p['catatan'];
                    }
                    if (!empty($p['qty'])) {
                        $mapQtyPilihan[$p['produk_id']] = (int) $p['qty'];
                    }
                }
            }
        }

        foreach ($bundling->items as $bundlingItem) {
            $produk = $bundlingItem->produk;
            if (! $produk) {
                continue;
            }

            $produk = Produk::lockForUpdate()->find($produk->id);
            if (!$produk) continue;

            // Kalau ada pilihan varian khusus, qty per produk ikut pilihan
            // member (varian yang tidak dipilih otomatis di-skip / qty 0).
            // Kalau tidak ada (bundling biasa), pakai qty tetap dari paket.
            // (Sama seperti TransaksiKasirController::prosesItemBundlingTransaksi().)
            $qtySatuBundling = $mapQtyPilihan[$produk->id] ?? (empty($pilihanKhusus) ? $bundlingItem->qty : 0);
            if ($qtySatuBundling <= 0) {
                continue;
            }
            $totalDibutuhkan = $qtySatuBundling * $jumlahBundling;
            $catatanProduk = $mapCatatanPilihan[$produk->id] ?? null;
            $ukuranItem = $bundlingItem->ukuran;

            $pecahan = $this->pecahJumlahProduksi($produk, $totalDibutuhkan, $ukuranItem, $stokTerpakai);

            foreach ($pecahan as $bagian) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'bundling_id' => $bundling->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $ukuranItem,
                    'harga_satuan' => 0,
                    'jumlah' => $bagian['jumlah'],
                    'catatan' => $catatanProduk !== '' ? $catatanProduk : null,
                    'subtotal' => 0,
                    'butuh_produksi' => $bagian['butuh_produksi'],
                ]);
            }
        }
    }

    private function prosesItemPromoEventCheckout(Transaksi $transaksi, array $item, array &$stokTerpakai): void
    {
        $promoEvent = PromoProdukEvent::with('produk')->findOrFail($item['promo_event_id']);
        $jumlahEvent = (int) $item['jumlah'];
        $produk = $promoEvent->produk;

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => null,
            'promo_event_id' => $promoEvent->id,
            'nama_produk' => $promoEvent->nama_event,
            'harga_satuan' => $item['harga_satuan'],
            'jumlah' => $jumlahEvent,
            'catatan' => $item['catatan'] ?? null,
            'subtotal' => $item['harga_satuan'] * $jumlahEvent,
            'butuh_produksi' => false,
        ]);

        if (! $produk) {
            return;
        }

        $ukuranEvent = $promoEvent->ukuranTunggalUntukProduksi();

        $totalDibutuhkan = $promoEvent->isi_event * $jumlahEvent;
        $pecahan = $this->pecahJumlahProduksi($produk, $totalDibutuhkan, $ukuranEvent, $stokTerpakai);

        foreach ($pecahan as $bagian) {
            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'promo_event_id' => $promoEvent->id,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $ukuranEvent,
                'harga_satuan' => 0,
                'jumlah' => $bagian['jumlah'],
                'catatan' => null,
                'subtotal' => 0,
                'butuh_produksi' => $bagian['butuh_produksi'],
            ]);
        }
    }

    private function pecahJumlahProduksi(Produk $produk, int $jumlah, ?string $ukuran, array &$stokTerpakai): array
    {
        if ($produk->status_ketersediaan === 'perlu_diproduksi') {
            return [['jumlah' => $jumlah, 'butuh_produksi' => true]];
        }

        // Kalau produk punya varian ukuran, cek stok sesuai ukuran yang dipilih
        // (Besar/Kecil), bukan stok gabungan.
        $kunci = $produk->id . '|' . ($ukuran ?? '-');
        $sudahDipakai = $stokTerpakai[$kunci] ?? 0;
        $stokTersedia = max(0, (int) floor((float) $produk->stokUntukUkuran($ukuran)) - $sudahDipakai);

        if ($stokTersedia >= $jumlah) {
            $stokTerpakai[$kunci] = $sudahDipakai + $jumlah;
            return [['jumlah' => $jumlah, 'butuh_produksi' => false]];
        }

        if ($stokTersedia <= 0) {
            return [['jumlah' => $jumlah, 'butuh_produksi' => true]];
        }

        $stokTerpakai[$kunci] = $sudahDipakai + $stokTersedia;

        return [
            ['jumlah' => $stokTersedia, 'butuh_produksi' => false],
            ['jumlah' => $jumlah - $stokTersedia, 'butuh_produksi' => true],
        ];
    }

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
        $pengaturan = \App\Models\PengaturanMember::aktifSekarang();
        if (! $pengaturan || $pengaturan->minimal_belanja <= 0) {
            return 0;
        }

        return (int) (floor($totalBayar / $pengaturan->minimal_belanja) * $pengaturan->poin);
    }
}