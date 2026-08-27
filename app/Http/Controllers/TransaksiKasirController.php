<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\PesananDitahan;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PengaturanMember;
use App\Models\Member;
use App\Models\Bundling;
use App\Models\PromoProdukEvent;
use App\Models\StampProgram;
use App\Models\MemberStamp;
use App\Models\StampRedemption;
use App\Models\PenukaranPoin;
use App\Models\PengaturanPajak;

class TransaksiKasirController extends Controller
{
    private function validasiVoucherUntukTransaksi(?int $voucherId, ?int $memberId): ?PenukaranPoin
    {
        if (!$voucherId) {
            return null;
        }

        if (!$memberId) {
            throw new \Exception('Voucher hanya bisa dipakai jika member sudah dipilih.');
        }

        $voucher = PenukaranPoin::with('reward')
            ->where('id', $voucherId)
            ->lockForUpdate()
            ->first();

        if (!$voucher || !$voucher->reward || $voucher->reward->jenis !== 'voucher') {
            throw new \Exception('Voucher tidak ditemukan.');
        }

        if ((int) $voucher->member_id !== (int) $memberId) {
            throw new \Exception('Voucher ini bukan milik member yang dipilih.');
        }

        if ($voucher->status !== 'menunggu_diambil') {
            throw new \Exception('Voucher ini sudah pernah dipakai atau dibatalkan.');
        }

        if ($voucher->sudahExpired()) {
            throw new \Exception('Voucher ini sudah kedaluwarsa.');
        }

        return $voucher;
    }
    private function tandaiVoucherDigunakan(PenukaranPoin $voucher, Transaksi $transaksi): void
    {
        $voucher->update([
            'status' => 'selesai',
            'diambil_pada' => now(),
            'transaksi_id' => $transaksi->id,
        ]);
    }
    private function validasiRewardStampUntukTransaksi(?int $stampRedemptionId, ?int $memberId): ?StampRedemption
    {
        if (!$stampRedemptionId) {
            return null;
        }

        if (!$memberId) {
            throw new \Exception('Reward stamp hanya bisa dipakai jika member sudah dipilih.');
        }

        $redemption = StampRedemption::with('produk')
            ->where('id', $stampRedemptionId)
            ->lockForUpdate()
            ->first();

        if (!$redemption) {
            throw new \Exception('Reward stamp tidak ditemukan.');
        }

        if ((int) $redemption->member_id !== (int) $memberId) {
            throw new \Exception('Reward stamp ini bukan milik member yang dipilih.');
        }

        if ($redemption->status !== 'menunggu_diambil') {
            throw new \Exception('Reward stamp ini sudah pernah diambil atau dibatalkan.');
        }

        if (!$redemption->produk) {
            throw new \Exception('Produk hadiah reward stamp ini tidak ditemukan.');
        }

        return $redemption;
    }

    private function prosesRewardStampTransaksi(Transaksi $transaksi, ?StampRedemption $redemption): void
    {
        if (!$redemption) {
            return;
        }

        $produk = $redemption->produk;

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama_produk,
            'harga_satuan' => 0,
            'jumlah' => $redemption->jumlah ?: 1,
            'catatan' => 'Reward Stamp',
            'subtotal' => 0,
            'butuh_produksi' => $this->butuhProduksi($produk),
        ]);

        $redemption->update([
            'status' => 'selesai',
            'diambil_pada' => now(),
            'transaksi_id' => $transaksi->id,
            'diproses_oleh' => auth()->id(),
        ]);
    }
    private function kembalikanRewardStampDariTransaksi(Transaksi $transaksi): void
    {
        StampRedemption::where('transaksi_id', $transaksi->id)->update([
            'status' => 'menunggu_diambil',
            'diambil_pada' => null,
            'transaksi_id' => null,
            'diproses_oleh' => null,
        ]);
    }

    private function butuhProduksi(Produk $produk): bool
    {
        // Kalau produk punya varian ukuran, dianggap perlu produksi hanya
        // kalau SEMUA ukuran (Besar & Kecil) sedang habis.
        return $produk->habisSemuaUkuran();
    }

    private function tentukanPerluProduksi(Produk $produk, array $item): bool
    {
        if ($produk->status_ketersediaan === 'perlu_diproduksi') {
            return true;
        }

        // Kalau produk punya varian ukuran, cek stok sesuai ukuran yang dipilih
        // di item (Besar/Kecil), bukan stok gabungan.
        return (bool) ($item['butuh_produksi'] ?? ($produk->stokUntukUkuran($item['ukuran'] ?? null) <= 0));
    }

    public function indeks()
    {
        try {
            $daftarProduk = Produk::with(['kategori', 'promos', 'produkUkurans.ukuran'])
                ->where('status_aktif', true)
                ->reguler()
                ->get();

            $daftarKategori = KategoriProduk::all();

            $daftarBundling = Bundling::with('items.produk')
                ->where('status_aktif', true)
                ->get()
                ->map(function ($bundling) {
                    $stokMaksimal = $bundling->items->reduce(function ($carry, $item) {
                        if (!$item->produk) {
                            return 0;
                        }
                        $bisaDibuat = intdiv((int) $item->produk->stok, max(1, $item->qty));
                        return is_null($carry) ? $bisaDibuat : min($carry, $bisaDibuat);
                    }, null) ?? 0;

                    $bundling->stok_bundling = $stokMaksimal;
                    $bundling->perlu_diproduksi = $stokMaksimal <= 0;
                    return $bundling;
                });

            $daftarPromoEvent = PromoProdukEvent::with('produk')
                ->berlaku()
                ->get();

            $daftarDitahan = PesananDitahan::with(['transaksi.voucher.reward', 'transaksi.stampRedemptions.produk'])->orderBy('created_at', 'desc')->get();
            $daftarMember = \App\Models\Member::orderBy('nama')->get();
            $pengaturanPajak = PengaturanPajak::getAktif();

            return view('pages.transaksi.indeks', [
                'title' => 'Transaksi Kasir',
                'daftarProduk' => $daftarProduk,
                'daftarKategori' => $daftarKategori,
                'daftarBundling' => $daftarBundling,
                'daftarPromoEvent' => $daftarPromoEvent,
                'daftarDitahan' => $daftarDitahan,
                'daftarMember' => $daftarMember,
                'pengaturanPajak' => $pengaturanPajak,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
    private function prosesItemBundlingTransaksi(Transaksi $transaksi, array $item): void
    {
        $bundlingId = (int) $item['bundling_id'];
        $jumlahBundling = (int) $item['jumlah'];
        $bundling = Bundling::with('items.produk')->findOrFail($bundlingId);

        $perluProduksiBundling = (bool) ($item['butuh_produksi'] ?? false);

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => null,
            'bundling_id' => $bundling->id,
            'nama_produk' => $bundling->nama_bundling,
            'harga_satuan' => $bundling->harga,
            'jumlah' => $jumlahBundling,
            'catatan' => $item['catatan'] ?? null,
            'subtotal' => $bundling->harga * $jumlahBundling,
            'butuh_produksi' => $perluProduksiBundling,
        ]);

        // Kalau bundling ini "khusus" (bebas pilih varian rasa), kasir milih
        // sendiri qty & catatan tiap varian lewat modal di POS — dikirim di
        // item['pilihan_khusus']. Kalau bundling biasa (bukan khusus), array
        // ini kosong dan kita fallback ke qty tetap dari bundling_items
        // seperti semula.
        $pilihanKhusus = $item['pilihan_khusus'] ?? [];
        $mapQtyPilihan = [];
        $mapCatatanPilihan = [];
        if (is_array($pilihanKhusus)) {
            foreach ($pilihanKhusus as $p) {
                if (isset($p['produk_id'])) {
                    if (isset($p['qty']) && (int) $p['qty'] > 0) {
                        $mapQtyPilihan[(int) $p['produk_id']] = (int) $p['qty'];
                    }
                    if (!empty($p['catatan'])) {
                        $mapCatatanPilihan[(int) $p['produk_id']] = $p['catatan'];
                    }
                }
            }
        }

        foreach ($bundling->items as $bundlingItem) {
            $produk = $bundlingItem->produk;
            if (!$produk) {
                continue;
            }

            // Kalau ada pilihan varian khusus, qty per produk ikut pilihan
            // kasir (varian yang tidak dipilih otomatis di-skip / qty 0).
            // Kalau tidak ada (bundling biasa), pakai qty tetap dari paket.
            $qtySatuBundling = $mapQtyPilihan[$produk->id] ?? (empty($pilihanKhusus) ? $bundlingItem->qty : 0);
            if ($qtySatuBundling <= 0) {
                continue;
            }

            $catatanProduk = $mapCatatanPilihan[$produk->id] ?? null;
            $totalDibutuhkan = $qtySatuBundling * $jumlahBundling;
            $statusManualProduksi = $produk->status_ketersediaan === 'perlu_diproduksi';

            $jumlahReady = 0;
            $jumlahProduksi = $totalDibutuhkan;

            if (!$statusManualProduksi) {
                $stokTersedia = max(0, (float) $produk->stok);
                $jumlahReady = min($stokTersedia, $totalDibutuhkan);
                $jumlahProduksi = $totalDibutuhkan - $jumlahReady;

                if ($jumlahReady > 0) {
                    $produk->decrement('stok', $jumlahReady);
                }
            }

            if ($jumlahReady > 0) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'bundling_id' => $bundling->id,
                    'nama_produk' => $produk->nama_produk,
                    'harga_satuan' => 0,
                    'jumlah' => $jumlahReady,
                    'catatan' => $catatanProduk,
                    'subtotal' => 0,
                    'butuh_produksi' => false,
                ]);
            }

            if ($jumlahProduksi > 0) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'bundling_id' => $bundling->id,
                    'nama_produk' => $produk->nama_produk,
                    'harga_satuan' => 0,
                    'jumlah' => $jumlahProduksi,
                    'catatan' => $catatanProduk,
                    'subtotal' => 0,
                    'butuh_produksi' => true,
                ]);
            }
        }
    }

    private function prosesItemPromoEventTransaksi(Transaksi $transaksi, array $item): void
    {
        $promoEventId = (int) $item['promo_event_id'];
        $jumlahEvent = (int) $item['jumlah'];
        $promoEvent = PromoProdukEvent::with('produk')->findOrFail($promoEventId);
        $produk = $promoEvent->produk;

        $perluProduksiEvent = (bool) ($item['butuh_produksi'] ?? false);

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => null,
            'promo_event_id' => $promoEvent->id,
            'nama_produk' => $promoEvent->nama_event,
            'harga_satuan' => $promoEvent->harga_event,
            'jumlah' => $jumlahEvent,
            'catatan' => $item['catatan'] ?? null,
            'subtotal' => $promoEvent->harga_event * $jumlahEvent,
            'butuh_produksi' => $perluProduksiEvent,
        ]);

        if (!$produk) {
            return;
        }

        // Event bisa dibatasi ke ukuran tertentu (mis. cuma "Jumbo"). Cek &
        // kurangi stok per ukuran itu (bukan kolom stok gabungan), dan catat
        // ukurannya di detail transaksi supaya kelihatan di POS — sama
        // seperti alur mobile (lihat CheckoutController::prosesItemPromoEventCheckout()).
        $ukuranEvent = $promoEvent->ukuranTunggalUntukProduksi();

        $totalDibutuhkan = $promoEvent->isi_event * $jumlahEvent;
        $statusManualProduksi = $produk->status_ketersediaan === 'perlu_diproduksi';

        $jumlahReady = 0;
        $jumlahProduksi = $totalDibutuhkan;

        if (!$statusManualProduksi) {
            $stokTersedia = max(0, (float) $produk->stokUntukUkuran($ukuranEvent));
            $jumlahReady = min($stokTersedia, $totalDibutuhkan);
            $jumlahProduksi = $totalDibutuhkan - $jumlahReady;

            if ($jumlahReady > 0) {
                $produk->decrementStokUkuran($ukuranEvent, $jumlahReady);
            }
        }

        if ($jumlahReady > 0) {
            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'promo_event_id' => $promoEvent->id,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $ukuranEvent,
                'harga_satuan' => 0,
                'jumlah' => $jumlahReady,
                'catatan' => null,
                'subtotal' => 0,
                'butuh_produksi' => false,
            ]);
        }

        if ($jumlahProduksi > 0) {
            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'promo_event_id' => $promoEvent->id,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $ukuranEvent,
                'harga_satuan' => 0,
                'jumlah' => $jumlahProduksi,
                'catatan' => null,
                'subtotal' => 0,
                'butuh_produksi' => true,
            ]);
        }
    }

    private function kembalikanStokDariDetail($detailTransaksi): void
    {
        foreach ($detailTransaksi as $detail) {
            if (!$detail->produk_id) {
                continue;
            }
            if (!$detail->butuh_produksi) {
                try {
                    $produk = Produk::find($detail->produk_id);
                    if ($produk) {
                        // Kembalikan ke stok ukuran (Besar/Kecil) yang sama dengan
                        // saat dikurangi, supaya stok per ukuran tetap sinkron.
                        $produk->incrementStokUkuran($detail->ukuran, $detail->jumlah);
                    }
                } catch (\Throwable $e) {
                    Log::error('Gagal mengembalikan stok dari detail transaksi', [
                        'detail_transaksi_id' => $detail->id ?? null,
                        'produk_id' => $detail->produk_id,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        }
    }

    public function simpanDitahan(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nama_pembeli' => 'nullable|string|max:255',
                'member_id' => 'nullable|exists:members,id',
                'voucher_id' => 'nullable|integer|exists:penukaran_poin,id',
                'stamp_redemption_id' => 'nullable|integer|exists:stamp_redemptions,id',
                'item_pesanan' => 'required|array',
                'subtotal' => 'required|numeric',
                'pajak' => 'required|numeric',
                'diskon' => 'required|numeric',
                'diskon_persen' => 'nullable|numeric',
                'diskon_label' => 'nullable|string|max:255',
                'total' => 'required|numeric',
                'catatan' => 'nullable|string',
            ]);

            $pengaturanPajak = PengaturanPajak::getAktif();
            if (!$pengaturanPajak->status_aktif) {
                $validated['pajak'] = 0;
            }

            $namaPembeli = $validated['nama_pembeli'] ?? 'Pelanggan Umum';

            $voucher = $this->validasiVoucherUntukTransaksi(
                $validated['voucher_id'] ?? null,
                $validated['member_id'] ?? null
            );
            $rewardStampSiap = $this->validasiRewardStampUntukTransaksi(
                $validated['stamp_redemption_id'] ?? null,
                $validated['member_id'] ?? null
            );

            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_pembeli' => $namaPembeli,
                'member_id' => $validated['member_id'] ?? null,
                'voucher_id' => $voucher?->id,
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'diskon_persen' => $validated['diskon_persen'] ?? 0,
                'diskon_label' => $request->diskon_label,
                'total_bayar' => $validated['total'],
                'jumlah_diterima' => 0,
                'kembalian' => 0,
                'metode_pembayaran' => 'Belum Bayar',
                'status_pembayaran' => 'Belum Lunas',
            ]);

            $this->prosesRewardStampTransaksi($transaksi, $rewardStampSiap);

            foreach ($validated['item_pesanan'] as $item) {

                if (!empty($item['is_reward_stamp'])) {
                    continue;
                }

                if (!empty($item['is_bundling']) && !empty($item['bundling_id'])) {
                    $this->prosesItemBundlingTransaksi($transaksi, $item);
                    continue;
                }

                if (!empty($item['is_promo_event']) && !empty($item['promo_event_id'])) {
                    $this->prosesItemPromoEventTransaksi($transaksi, $item);
                    continue;
                }

                 $produk = Produk::findOrFail($item['produk_id']);
                $perluProduksi = $this->tentukanPerluProduksi($produk, $item);
                $ukuranItem = $item['ukuran'] ?? null;

                if (!$perluProduksi) {
                    $stokTersediaUntukUkuran = $produk->stokUntukUkuran($ukuranItem);
                    if ($stokTersediaUntukUkuran < $item['jumlah']) {
                        throw new \Exception("Stok untuk produk '{$produk->nama_produk}'" . ($ukuranItem ? " ukuran {$ukuranItem}" : '') . " tidak mencukupi (Tersedia: {$stokTersediaUntukUkuran}, Diminta: {$item['jumlah']})");
                    }

                    try {
                        // Kurangi stok sesuai ukuran (Besar/Kecil) yang dipilih kasir.
                        $produk->decrementStokUkuran($ukuranItem, $item['jumlah']);
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengurangi stok saat menahan pesanan', [
                            'produk_id' => $produk->id,
                            'ukuran' => $ukuranItem,
                            'jumlah' => $item['jumlah'],
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                }

                $detail = DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $ukuranItem,
                    'harga_satuan' => $item['harga_jual'],
                    'jumlah' => $item['jumlah'],
                    'catatan' => $item['catatan'] ?? null,
                    'subtotal' => $item['harga_jual'] * $item['jumlah'],
                ]);

                $detail->butuh_produksi = $perluProduksi;
                $detail->save();
            }

             $pesanan = PesananDitahan::create([
                'nama_pembeli' => $namaPembeli,
                'item_pesanan' => $validated['item_pesanan'],
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'diskon_persen' => $validated['diskon_persen'] ?? 0,
                'diskon_label' => $request->diskon_label,
                'total' => $validated['total'],
                'catatan' => $validated['catatan'],
                'transaksi_id' => $transaksi->id,
            ]);

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pesanan)
                ->withProperties([
                    'pesanan_id' => $pesanan->id,
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $kodeTransaksi,
                    'nama_pembeli' => $pesanan->nama_pembeli,
                ])
                ->log('menahan pesanan (bayar nanti)');

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan berhasil masuk ke daftar Belum Dibayar.',
                'data' => $pesanan->load('transaksi'),
                'kode_transaksi' => $kodeTransaksi,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function ambilDitahan()
    {
        try {
            $daftarDitahan = PesananDitahan::with(['transaksi.voucher.reward', 'transaksi.stampRedemptions.produk'])->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $daftarDitahan
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function pulihkanDitahan($id)
    {
        DB::beginTransaction();
        try {
            $pesanan = PesananDitahan::with(['transaksi.voucher.reward', 'transaksi.stampRedemptions.produk'])->findOrFail($id);
             if ($pesanan->transaksi) {
                $detailTransaksi = DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->get();
                $this->kembalikanStokDariDetail($detailTransaksi);
                 $this->kembalikanRewardStampDariTransaksi($pesanan->transaksi);

                 DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->delete();
                $pesanan->transaksi->delete();
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pesanan)
                ->withProperties([
                    'pesanan_id' => $pesanan->id,
                    'nama_pembeli' => $pesanan->nama_pembeli,
                ])
                ->log('memulihkan pesanan ditahan');

            $pesanan->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan berhasil dilanjutkan.',
                'data' => $pesanan
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapusDitahan($id)
    {
        DB::beginTransaction();
        try {
            $pesanan = PesananDitahan::with('transaksi')->findOrFail($id);

            if ($pesanan->transaksi) {
                $detailTransaksi = DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->get();
                $this->kembalikanStokDariDetail($detailTransaksi);

                $this->kembalikanRewardStampDariTransaksi($pesanan->transaksi);

                DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->delete();
                $pesanan->transaksi->delete();
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pesanan)
                ->withProperties([
                    'pesanan_id' => $pesanan->id,
                    'nama_pembeli' => $pesanan->nama_pembeli,
                ])
                ->log('menghapus pesanan ditahan');

            $pesanan->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan ditahan berhasil dihapus.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function lunaskanDitahan(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pesanan = PesananDitahan::with('transaksi')->findOrFail($id);

            if (!$pesanan->transaksi) {
                throw new \Exception('Transaksi terkait tidak ditemukan.');
            }

            $validated = $request->validate([
                'jumlah_diterima' => 'required|numeric',
                'kembalian' => 'required|numeric',
                'metode_pembayaran' => 'required|string',
                'member_id' => 'nullable|exists:members,id',
                'voucher_id' => 'nullable|integer|exists:penukaran_poin,id',
                'diskon_label' => 'nullable|string|max:255',
            ]);

            $transaksi = $pesanan->transaksi;
            $memberIdEfektif = $validated['member_id'] ?? $transaksi->member_id;

               $voucherDipakai = $this->validasiVoucherUntukTransaksi(
                $request->has('voucher_id') ? ($validated['voucher_id'] ?? null) : $transaksi->voucher_id,
                $memberIdEfektif
            );

             $transaksi->update([
                'jumlah_diterima' => $validated['jumlah_diterima'],
                'kembalian' => $validated['kembalian'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'member_id' => $memberIdEfektif,
                'voucher_id' => $voucherDipakai?->id,
                'status_pembayaran' => 'Lunas',
                'diskon_label' => $validated['diskon_label'] ?? $transaksi->diskon_label,
            ]);

               if ($voucherDipakai) {
                $this->tandaiVoucherDigunakan($voucherDipakai, $transaksi);
            }

             if (!empty($validated['member_id'])) {
                $nominalUntukPoin = $transaksi->subtotal + $transaksi->pajak;
                $member = \App\Models\Member::find($validated['member_id']);
                if ($member) {
                    $poinSebelum = $member->poin;
                    $poinDiperoleh = 0;
                    if ($nominalUntukPoin >= 100000) {
                        $poinDiperoleh = floor($nominalUntukPoin / 100000) * 100;
                        if ($poinDiperoleh > 0) {
                            $member->increment('poin', (int) $poinDiperoleh);
                        }
                    }
                    $transaksi->update([
                        'poin_sebelum' => $poinSebelum,
                        'poin_didapat' => (int) $poinDiperoleh,
                    ]);
                }
            }

             $pesanan->delete();

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($transaksi)
                ->withProperties([
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'nama_pembeli' => $transaksi->nama_pembeli,
                ])
                ->log('melunaskan pesanan');

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan berhasil dilunaskan.',
                'kode_transaksi' => $transaksi->kode_transaksi,
                'data' => $transaksi
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function bayar(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nama_pembeli' => 'nullable|string|max:255',
                'member_id' => 'nullable|exists:members,id',
                'voucher_id' => 'nullable|integer|exists:penukaran_poin,id',
                'stamp_redemption_id' => 'nullable|integer|exists:stamp_redemptions,id',
                'item_pesanan' => 'required|array',
                'subtotal' => 'required|numeric',
                'pajak' => 'required|numeric',
                'diskon' => 'required|numeric',
                'diskon_persen' => 'nullable|numeric',
                'diskon_label' => 'nullable|string|max:255',
                'total_bayar' => 'required|numeric',
                'jumlah_diterima' => 'required|numeric',
                'kembalian' => 'required|numeric',
                'metode_pembayaran' => 'required|string',
            ]);

            $pengaturanPajak = PengaturanPajak::getAktif();
            if (!$pengaturanPajak->status_aktif) {
                $validated['pajak'] = 0;
            }

             $voucherDipakai = $this->validasiVoucherUntukTransaksi(
                $validated['voucher_id'] ?? null,
                $validated['member_id'] ?? null
            );

               $rewardStampSiap = $this->validasiRewardStampUntukTransaksi(
                $validated['stamp_redemption_id'] ?? null,
                $validated['member_id'] ?? null
            );

              $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_pembeli' => $validated['nama_pembeli'] ?? 'Pelanggan Umum',
                'member_id' => $validated['member_id'] ?? null,
                'voucher_id' => $voucherDipakai?->id,
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'diskon_persen' => $validated['diskon_persen'] ?? 0,
                'diskon_label' => $request->diskon_label,
                'total_bayar' => $validated['total_bayar'],
                'jumlah_diterima' => $validated['jumlah_diterima'],
                'kembalian' => $validated['kembalian'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => 'Lunas',
            ]);

              if ($voucherDipakai) {
                $this->tandaiVoucherDigunakan($voucherDipakai, $transaksi);
            }

              $this->prosesRewardStampTransaksi($transaksi, $rewardStampSiap);

             if ($transaksi->member_id) {
                $pengaturan = PengaturanMember::where('status_aktif', true)->first();

                if ($pengaturan) {
                    $nominalUntukPoin = $transaksi->subtotal + $transaksi->pajak;
                    $kelipatan = floor(
                        $nominalUntukPoin / $pengaturan->minimal_belanja
                    );
                    $poin = $kelipatan * $pengaturan->poin;

                    $member = Member::find($transaksi->member_id);
                    if ($member) {
                        $poinSebelum = $member->poin;
                        if ($poin > 0) {
                            $member->increment('poin', $poin);
                        }
                        $transaksi->update([
                            'poin_sebelum' => $poinSebelum,
                            'poin_didapat' => (int) $poin,
                        ]);
                    }
                }
            }

             if ($transaksi->member_id) {
                $stampProgram = StampProgram::aktifSekarang();

                if ($stampProgram) {
                    $nominalUntukStamp = $transaksi->subtotal + $transaksi->pajak;
                    $stampDidapat = (int) floor($nominalUntukStamp / $stampProgram->minimal_pembelian);

                    if ($stampDidapat > 0) {
                        $memberStamp = MemberStamp::firstOrCreate(
                            ['member_id' => $transaksi->member_id, 'stamp_program_id' => $stampProgram->id],
                            ['jumlah_stamp' => 0]
                        );

                        $stampSebelum = $memberStamp->jumlah_stamp;
                         $stampBaru = min(
                            $stampSebelum + $stampDidapat,
                            $stampProgram->jumlah_stamp_dibutuhkan
                        );

                        $memberStamp->update(['jumlah_stamp' => $stampBaru]);

                        $transaksi->update([
                            'stamp_program_id' => $stampProgram->id,
                            'stamp_sebelum' => $stampSebelum,
                            'stamp_didapat' => $stampBaru - $stampSebelum,
                        ]);
                    }
                }
            }

            foreach ($validated['item_pesanan'] as $item) {

                 if (!empty($item['is_reward_stamp'])) {
                    continue;
                }

                 if (!empty($item['is_bundling']) && !empty($item['bundling_id'])) {
                    $this->prosesItemBundlingTransaksi($transaksi, $item);
                    continue;
                }

                if (!empty($item['is_promo_event']) && !empty($item['promo_event_id'])) {
                    $this->prosesItemPromoEventTransaksi($transaksi, $item);
                    continue;
                }

                 $produk = Produk::findOrFail($item['produk_id']);
                $perluProduksi = $this->tentukanPerluProduksi($produk, $item);
                $ukuranItem = $item['ukuran'] ?? null;

                if (!$perluProduksi) {
                    $stokTersediaUntukUkuran = $produk->stokUntukUkuran($ukuranItem);
                    if ($stokTersediaUntukUkuran < $item['jumlah']) {
                        throw new \Exception("Stok untuk produk '{$produk->nama_produk}'" . ($ukuranItem ? " ukuran {$ukuranItem}" : '') . " tidak mencukupi (Tersedia: {$stokTersediaUntukUkuran}, Diminta: {$item['jumlah']})");
                    }

                    try {
                        // Kurangi stok sesuai ukuran (Besar/Kecil) yang dipilih kasir.
                        $produk->decrementStokUkuran($ukuranItem, $item['jumlah']);
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengurangi stok saat transaksi kasir', [
                            'produk_id' => $produk->id,
                            'ukuran' => $ukuranItem,
                            'jumlah' => $item['jumlah'],
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                }

                $detail = DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $ukuranItem,
                    'harga_satuan' => $item['harga_jual'],
                    'jumlah' => $item['jumlah'],
                    'catatan' => $item['catatan'] ?? null,
                    'subtotal' => $item['harga_jual'] * $item['jumlah'],
                ]);

                $detail->butuh_produksi = $perluProduksi;
                $detail->save();
            }

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($transaksi)
                ->withProperties([
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $kodeTransaksi,
                    'nama_pembeli' => $transaksi->nama_pembeli,
                ])
                ->log('menyelesaikan transaksi');

            return response()->json([
                'status' => 'success',
                'pesan' => 'Transaksi berhasil diselesaikan.',
                'kode_transaksi' => $kodeTransaksi,
                'data' => $transaksi
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function cetak(Request $request, $kodeTransaksi)
    {
        try {
            $transaksi = Transaksi::with(['detailTransaksi.produk', 'member'])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();
            $jenis = $request->get('jenis', 'pelanggan');

            return view('pages.transaksi.cetak', [
                'transaksi' => $transaksi,
                'jenis' => $jenis,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}