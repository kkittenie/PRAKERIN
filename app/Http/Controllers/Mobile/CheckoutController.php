<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Models\PromoProdukEvent;
use App\Models\DetailTransaksi;
use App\Models\Member;
use App\Models\RewardKatalog;
use App\Models\VoucherUsage;
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

            $voucherAktif = RewardKatalog::with('produks')
                ->where('jenis', 'voucher')
                ->where('status_aktif', true)
                ->where(function ($q) use ($member) {
                    $q->where('target_member', 'semua')
                      ->orWhere(function ($sub) use ($member) {
                          $sub->where('target_member', 'khusus')
                              ->whereHas('members', fn ($m) => $m->where('members.id', $member->id));
                      });
                })
                ->get()
                ->filter(fn (RewardKatalog $v) => $v->berhakDigunakan($member))
                ->values();

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
                'voucher_id' => 'nullable|integer|exists:reward_katalog,id',
            ]);

            if (empty($validated['voucher_id'])) {
                Session::forget(self::SESSION_VOUCHER_KEY);
                return back();
            }

            $member = Auth::guard('member')->user();
            $voucher = RewardKatalog::find($validated['voucher_id']);

            if (! $voucher || ! $voucher->berhakDigunakan($member)) {
                return back()->with('error', 'Voucher tidak dapat digunakan atau batas penggunaan telah tercapai.');
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
                    $voucher = RewardKatalog::with('produks')
                        ->where('id', $voucherId)
                        ->where('jenis', 'voucher')
                        ->lockForUpdate()
                        ->first();

                    if (! $voucher || ! $voucher->berhakDigunakan($member)) {
                        throw new \Exception('Voucher yang dipilih sudah tidak berlaku atau batas penggunaan tercapai, silakan pilih ulang.');
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
                        'nama' => $voucher->nama ?? 'Voucher',
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
                    VoucherUsage::create([
                        'reward_katalog_id' => $voucher->id,
                        'member_id' => $member->id,
                        'transaksi_id' => $transaksi->id,
                        'used_at' => now(),
                    ]);
                    Session::forget(self::SESSION_VOUCHER_KEY);
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
                    $varianRasaItem = $item['varian_rasa'] ?? null;
                    $hargaSatuanDetail = (float) ($item['harga_asal'] ?? $item['harga_satuan']);

                    $varian = null;
                    if (!empty($item['produk_varian_id'])) {
                        $varian = \App\Models\ProdukVarian::lockForUpdate()->find($item['produk_varian_id']);
                    } elseif ($produk->produkVarians()->exists()) {
                        $varian = $produk->produkVarians()
                            ->where(function ($q) use ($ukuranItem, $varianRasaItem) {
                                if ($ukuranItem) $q->where('ukuran', $ukuranItem);
                                if ($varianRasaItem) $q->where('variasi', $varianRasaItem);
                            })->first();
                    }

                    if ($varian) {
                        $jumlahDibutuhkan = (int) $item['jumlah'];
                        $kunciVarian = 'varian_' . $varian->id;
                        $sudahDipakai = (int) ($stokTerpakai[$kunciVarian] ?? 0);
                        $stokTersedia = max(0, (int) $varian->stok - $sudahDipakai);

                        if ($varian->status_aktif && $stokTersedia > 0) {
                            $stokReady = min($stokTersedia, $jumlahDibutuhkan);
                            $stokProduksi = $jumlahDibutuhkan - $stokReady;
                        } else {
                            $stokReady = 0;
                            $stokProduksi = $jumlahDibutuhkan;
                        }

                        if ($stokReady > 0) {
                            $stokTerpakai[$kunciVarian] = $sudahDipakai + $stokReady;

                            DetailTransaksi::create([
                                'transaksi_id' => $transaksi->id,
                                'produk_id' => $produk->id,
                                'produk_varian_id' => $varian->id,
                                'nama_produk' => $produk->nama_produk,
                                'ukuran' => $varian->ukuran,
                                'varian_rasa' => $varian->variasi,
                                'harga_satuan' => $hargaSatuanDetail,
                                'jumlah' => $stokReady,
                                'catatan' => $catatanItem !== '' ? $catatanItem : null,
                                'subtotal' => $hargaSatuanDetail * $stokReady,
                                'butuh_produksi' => false,
                            ]);
                        }

                        if ($stokProduksi > 0) {
                            DetailTransaksi::create([
                                'transaksi_id' => $transaksi->id,
                                'produk_id' => $produk->id,
                                'produk_varian_id' => $varian->id,
                                'nama_produk' => $produk->nama_produk,
                                'ukuran' => $varian->ukuran,
                                'varian_rasa' => $varian->variasi,
                                'harga_satuan' => $hargaSatuanDetail,
                                'jumlah' => $stokProduksi,
                                'catatan' => $catatanItem !== '' ? $catatanItem : null,
                                'subtotal' => $hargaSatuanDetail * $stokProduksi,
                                'butuh_produksi' => true,
                            ]);
                        }
                    } else {
                        $pecahan = $this->pecahJumlahProduksi($produk, (int) $item['jumlah'], $ukuranItem, $varianRasaItem, $stokTerpakai);

                        foreach ($pecahan as $bagian) {
                            DetailTransaksi::create([
                                'transaksi_id' => $transaksi->id,
                                'produk_id' => $produk->id,
                                'nama_produk' => $produk->nama_produk,
                                'ukuran' => $ukuranItem,
                                'varian_rasa' => $varianRasaItem,
                                'harga_satuan' => $hargaSatuanDetail,
                                'jumlah' => $bagian['jumlah'],
                                'catatan' => $catatanItem !== '' ? $catatanItem : null,
                                'subtotal' => $hargaSatuanDetail * $bagian['jumlah'],
                                'butuh_produksi' => $bagian['butuh_produksi'],
                            ]);
                        }
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
        $bundling = Bundling::with('items.produk', 'items.produkVarian')->findOrFail($item['bundling_id']);
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
            $qtySatuBundling = $mapQtyPilihan[$produk->id] ?? (empty($pilihanKhusus) ? $bundlingItem->qty : 0);
            if ($qtySatuBundling <= 0) {
                continue;
            }
            $totalDibutuhkan = (int) ($qtySatuBundling * $jumlahBundling);
            $catatanProduk = $mapCatatanPilihan[$produk->id] ?? null;

            // ── Sistem baru: track stok via ProdukVarian ──
            if ($bundlingItem->produk_varian_id) {
                $varian = $bundlingItem->produkVarian;
                if (!$varian) continue;

                // Track stok yang sudah "dipakai" dalam checkout ini (belum dikurangi dari DB)
                $kunciVarian = 'varian_' . $varian->id;
                $sudahDipakai = (int) ($stokTerpakai[$kunciVarian] ?? 0);
                $stokEfektif = max(0, (int) $varian->stok - $sudahDipakai);

                $perluProduksi = $varian->perlu_diproduksi || $stokEfektif <= 0;

                if ($perluProduksi) {
                    // Semua masuk produksi
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $produk->id,
                        'produk_varian_id' => $varian->id,
                        'bundling_id' => $bundling->id,
                        'nama_produk' => $produk->nama_produk,
                        'ukuran' => $varian->ukuran,
                        'varian_rasa' => $varian->variasi,
                        'harga_satuan' => 0,
                        'jumlah' => $totalDibutuhkan,
                        'catatan' => $catatanProduk !== '' ? $catatanProduk : null,
                        'subtotal' => 0,
                        'butuh_produksi' => true,
                    ]);
                } else {
                    $stokReady = min($stokEfektif, $totalDibutuhkan);
                    $stokProduksi = $totalDibutuhkan - $stokReady;

                    if ($stokReady > 0) {
                        $stokTerpakai[$kunciVarian] = $sudahDipakai + $stokReady;

                        DetailTransaksi::create([
                            'transaksi_id' => $transaksi->id,
                            'produk_id' => $produk->id,
                            'produk_varian_id' => $varian->id,
                            'bundling_id' => $bundling->id,
                            'nama_produk' => $produk->nama_produk,
                            'ukuran' => $varian->ukuran,
                            'varian_rasa' => $varian->variasi,
                            'harga_satuan' => 0,
                            'jumlah' => $stokReady,
                            'catatan' => $catatanProduk !== '' ? $catatanProduk : null,
                            'subtotal' => 0,
                            'butuh_produksi' => false,
                        ]);
                    }

                    if ($stokProduksi > 0) {
                        DetailTransaksi::create([
                            'transaksi_id' => $transaksi->id,
                            'produk_id' => $produk->id,
                            'produk_varian_id' => $varian->id,
                            'bundling_id' => $bundling->id,
                            'nama_produk' => $produk->nama_produk,
                            'ukuran' => $varian->ukuran,
                            'varian_rasa' => $varian->variasi,
                            'harga_satuan' => 0,
                            'jumlah' => $stokProduksi,
                            'catatan' => $catatanProduk !== '' ? $catatanProduk : null,
                            'subtotal' => 0,
                            'butuh_produksi' => true,
                        ]);
                    }
                }

                continue;
            }

            // ── Fallback: sistem lama (ukuran + varian_rasa string) ──
            $ukuranItem = $bundlingItem->ukuran;
            $rasaItem = $produk->punya_varian_rasa ? ($bundlingItem->varian_rasa ?: null) : null;

            $pecahan = $this->pecahJumlahProduksiRasa($produk, $totalDibutuhkan, $ukuranItem, $rasaItem ? [$rasaItem] : [], $stokTerpakai);

            foreach ($pecahan as $bagian) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'bundling_id' => $bundling->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $ukuranItem,
                    'varian_rasa' => $bagian['varian_rasa'],
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
        $promoEvent = PromoProdukEvent::with('produk.produkVarians')->findOrFail($item['promo_event_id']);
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

        $totalDibutuhkan = (int) ($promoEvent->isi_event * $jumlahEvent);
        $ukuranEvent = $promoEvent->ukuranTunggalUntukProduksi();
        $kandidatRasa = $promoEvent->kandidatRasaUntukProduksi();

        // ── Sistem baru: cari ProdukVarian yang cocok ──
        $variansCocok = $produk->produkVarians
            ->filter(function ($v) use ($ukuranEvent, $kandidatRasa) {
                if (!$v->status_aktif) return false;
                if ($ukuranEvent && $v->ukuran && strcasecmp($v->ukuran, $ukuranEvent) !== 0) return false;
                if ($ukuranEvent && !$v->ukuran) return false;
                if (!empty($kandidatRasa)) {
                    $cocok = false;
                    foreach ($kandidatRasa as $rasa) {
                        if ($v->variasi && strcasecmp($v->variasi, $rasa) === 0) {
                            $cocok = true;
                            break;
                        }
                    }
                    if (!$cocok && $v->variasi) return false;
                }
                return true;
            })
            ->values();

        if ($variansCocok->isNotEmpty()) {
            $sisa = $totalDibutuhkan;

            foreach ($variansCocok as $varian) {
                if ($sisa <= 0) break;

                $kunciVarian = 'varian_' . $varian->id;
                $sudahDipakai = (int) ($stokTerpakai[$kunciVarian] ?? 0);
                $stokEfektif = max(0, (int) $varian->stok - $sudahDipakai);

                $perluProduksi = $varian->perlu_diproduksi || $stokEfektif <= 0;

                if (!$perluProduksi && $stokEfektif > 0) {
                    $ambil = min($stokEfektif, $sisa);
                    $stokTerpakai[$kunciVarian] = $sudahDipakai + $ambil;

                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $produk->id,
                        'produk_varian_id' => $varian->id,
                        'promo_event_id' => $promoEvent->id,
                        'nama_produk' => $produk->nama_produk,
                        'ukuran' => $varian->ukuran,
                        'varian_rasa' => $varian->variasi,
                        'harga_satuan' => 0,
                        'jumlah' => $ambil,
                        'catatan' => null,
                        'subtotal' => 0,
                        'butuh_produksi' => false,
                    ]);

                    $sisa -= $ambil;
                }
            }

            if ($sisa > 0) {
                $varianProduksi = $variansCocok->first();

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'produk_varian_id' => $varianProduksi->id,
                    'promo_event_id' => $promoEvent->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $varianProduksi->ukuran,
                    'varian_rasa' => $varianProduksi->variasi,
                    'harga_satuan' => 0,
                    'jumlah' => $sisa,
                    'catatan' => null,
                    'subtotal' => 0,
                    'butuh_produksi' => true,
                ]);
            }

            return;
        }

        // ── Fallback: sistem lama ──
        $pecahan = $this->pecahJumlahProduksiRasa($produk, $totalDibutuhkan, $ukuranEvent, $kandidatRasa, $stokTerpakai);

        foreach ($pecahan as $bagian) {
            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'promo_event_id' => $promoEvent->id,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $ukuranEvent,
                'varian_rasa' => $bagian['varian_rasa'],
                'harga_satuan' => 0,
                'jumlah' => $bagian['jumlah'],
                'catatan' => null,
                'subtotal' => 0,
                'butuh_produksi' => $bagian['butuh_produksi'],
            ]);
        }
    }

    private function pecahJumlahProduksi(Produk $produk, int $jumlah, ?string $ukuran, ?string $varianRasa, array &$stokTerpakai): array
    {
        return $this->pecahJumlahProduksiRasa($produk, $jumlah, $ukuran, $varianRasa ? [$varianRasa] : [], $stokTerpakai);
    }

    /**
     * Pecah $jumlah jadi bagian "ready" (stok ukuran x rasa cukup) dan bagian
     * "butuh produksi". $kandidatRasa = rasa yang boleh dipakai (satu rasa untuk
     * item bundling / produk biasa, beberapa untuk promo event). Tiap bagian
     * mengembalikan rasa yang dipakai supaya bisa dicatat di detail transaksi.
     *
     * Stok BELUM dikurangi di sini (baru dikurangi saat pesanan diproses),
     * makanya $stokTerpakai dipakai buat menghitung pemakaian dalam satu checkout.
     */
    private function pecahJumlahProduksiRasa(Produk $produk, int $jumlah, ?string $ukuran, array $kandidatRasa, array &$stokTerpakai): array
    {
        $alokasi = $produk->alokasiStok($ukuran, $kandidatRasa, $jumlah, $stokTerpakai);

        $hasil = [];
        foreach ($alokasi['ready'] as $bagian) {
            $hasil[] = [
                'jumlah' => $bagian['jumlah'],
                'butuh_produksi' => false,
                'varian_rasa' => $bagian['varian_rasa'],
            ];
        }

        if ($alokasi['produksi'] > 0) {
            $hasil[] = [
                'jumlah' => $alokasi['produksi'],
                'butuh_produksi' => true,
                // Rasa sisa produksi cuma bisa ditentukan kalau kandidatnya satu.
                'varian_rasa' => count($kandidatRasa) === 1 ? $kandidatRasa[0] : null,
            ];
        }

        return $hasil;
    }

    private function hitungDiskonVoucher(RewardKatalog $voucher, $items, float $subtotal): float
    {
        if ((float) ($voucher->min_pembelian ?? 0) > $subtotal) {
            return 0;
        }

        $produkIds = $voucher->produks->pluck('id')->values()->all();

        $basis = $subtotal;
        if (! empty($produkIds)) {
            $basis = collect($items)
                ->filter(fn ($item) => in_array($item['produk_id'] ?? null, $produkIds, true))
                ->sum(fn ($item) => $item['harga_satuan'] * $item['jumlah']);
        }

        if ($voucher->jenis_potongan === 'nominal') {
            $diskon = (float) $voucher->nominal_potongan;
        } else {
            $diskon = $basis * ((float) $voucher->persentase / 100);
            if (! is_null($voucher->maksimal_potongan)) {
                $diskon = min($diskon, (float) $voucher->maksimal_potongan);
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