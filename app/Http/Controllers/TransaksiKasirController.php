<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\PesananDitahan;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PengaturanMember;
use App\Models\Member;
use App\Models\Bundling;

class TransaksiKasirController extends Controller
{
    private function butuhProduksi(Produk $produk): bool
    {
        return $produk->status_ketersediaan === 'perlu_diproduksi' || (float) $produk->stok <= 0;
    }

    private function tentukanPerluProduksi(Produk $produk, array $item): bool
    {
        if ($produk->status_ketersediaan === 'perlu_diproduksi') {
            return true;
        }

        return (bool) ($item['butuh_produksi'] ?? ((float) $produk->stok <= 0));
    }

    public function indeks()
    {
        try {
            $daftarProduk = Produk::with(['kategori', 'promos'])
                ->where('status_aktif', true)
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

            $daftarDitahan = PesananDitahan::with('transaksi')->orderBy('created_at', 'desc')->get();
            $daftarMember = \App\Models\Member::orderBy('nama')->get();

            return view('pages.transaksi.indeks', [
                'title' => 'Transaksi Kasir',
                'daftarProduk' => $daftarProduk,
                'daftarKategori' => $daftarKategori,
                'daftarBundling' => $daftarBundling,
                'daftarDitahan' => $daftarDitahan,
                'daftarMember' => $daftarMember,
            ]);
        } catch (\Exception $e) {
            Log::error('Error pada indeks TransaksiKasirController: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $e->getMessage(),
                ])
                ->log('gagal memuat data transaksi');
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Proses satu item bundling dalam transaksi.
     *
     * Untuk tiap komponen produk di dalam bundling, kebutuhan dipecah
     * menjadi 2 baris DetailTransaksi kalau perlu:
     *  - baris "ready"     -> jumlah yang kepakai dari stok (butuh_produksi=false)
     *  - baris "produksi"  -> sisa kebutuhan yang stoknya gak cukup (butuh_produksi=true)
     * Ini supaya struk dapur cuma nampilin jumlah yang BENERAN perlu diproduksi,
     * bukan seluruh kebutuhan komponen itu.
     */
    private function prosesItemBundlingTransaksi(Transaksi $transaksi, array $item): void
    {
        $bundlingId = (int) $item['bundling_id'];
        $jumlahBundling = (int) $item['jumlah'];
        $bundling = Bundling::with('items.produk')->findOrFail($bundlingId);

        // 1. Entry Bundling Utama untuk Struk Pelanggan
        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => null,
            'bundling_id' => $bundling->id,
            'nama_produk' => $bundling->nama_bundling,
            'harga_satuan' => $bundling->harga,
            'jumlah' => $jumlahBundling,
            'catatan' => $item['catatan'] ?? null,
            'subtotal' => $bundling->harga * $jumlahBundling,
            'butuh_produksi' => false,
        ]);

        // 2. Entry Komponen Produk (Stok & Struk Dapur)
        foreach ($bundling->items as $bundlingItem) {
            $produk = $bundlingItem->produk;
            if (!$produk) {
                continue;
            }

            $totalDibutuhkan = $bundlingItem->qty * $jumlahBundling;
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
                    'catatan' => null,
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
                    'catatan' => null,
                    'subtotal' => 0,
                    'butuh_produksi' => true,
                ]);
            }
        }
    }

    /**
     * Kembalikan stok berdasarkan baris-baris DetailTransaksi (dipakai baik
     * untuk item produk biasa maupun komponen bundling). Baris ringkasan
     * bundling (produk_id null) dilewati karena baris itu tidak pernah
     * memotong stok. Baris yang butuh_produksi=true juga dilewati karena
     * stoknya memang tidak pernah dipotong.
     */
    private function kembalikanStokDariDetail($detailTransaksi): void
    {
        foreach ($detailTransaksi as $detail) {
            if (!$detail->produk_id) {
                continue;
            }
            if (!$detail->butuh_produksi) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    $produk->increment('stok', $detail->jumlah);
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
                'item_pesanan' => 'required|array',
                'subtotal' => 'required|numeric',
                'pajak' => 'required|numeric',
                'diskon' => 'required|numeric',
                'diskon_label' => 'nullable|string|max:255',
                'total' => 'required|numeric',
                'catatan' => 'nullable|string',
            ]);

            $namaPembeli = $validated['nama_pembeli'] ?? 'Pelanggan Umum';

            // Buat kode transaksi unik
            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            // Buat transaksi dengan status Belum Lunas
            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_pembeli' => $namaPembeli,
                'member_id' => $validated['member_id'] ?? null,
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'diskon_label' => $request->diskon_label,
                'total_bayar' => $validated['total'],
                'jumlah_diterima' => 0,
                'kembalian' => 0,
                'metode_pembayaran' => 'Belum Bayar',
                'status_pembayaran' => 'Belum Lunas',
            ]);

            foreach ($validated['item_pesanan'] as $item) {

                // ==== Item BUNDLING ====
                if (!empty($item['is_bundling']) && !empty($item['bundling_id'])) {
                    $this->prosesItemBundlingTransaksi($transaksi, $item);
                    continue;
                }

                // ==== Item PRODUK biasa (logic lama, tidak berubah) ====
                $produk = Produk::findOrFail($item['produk_id']);
                $perluProduksi = $this->tentukanPerluProduksi($produk, $item);

                if (!$perluProduksi && $produk->stok < $item['jumlah']) {
                    throw new \Exception("Stok untuk produk '{$produk->nama_produk}' tidak mencukupi (Tersedia: {$produk->stok}, Diminta: {$item['jumlah']})");
                }

                if (!$perluProduksi) {
                    $produk->decrement('stok', $item['jumlah']);
                }

                $detail = DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'harga_satuan' => $item['harga_jual'],
                    'jumlah' => $item['jumlah'],
                    'catatan' => $item['catatan'] ?? null,
                    'subtotal' => $item['harga_jual'] * $item['jumlah'],
                ]);

                $detail->butuh_produksi = $perluProduksi;
                $detail->save();
            }

            // Simpan pesanan ditahan dengan link ke transaksi
            $pesanan = PesananDitahan::create([
                'nama_pembeli' => $namaPembeli,
                'item_pesanan' => $validated['item_pesanan'],
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pada simpanDitahan TransaksiKasirController: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $e->getMessage(),
                    'request_data' => $request->all(),
                ])
                ->log('gagal menahan pesanan');
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal menahan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ambilDitahan()
    {
        try {
            $daftarDitahan = PesananDitahan::with('transaksi')->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $daftarDitahan
            ]);
        } catch (\Exception $e) {
            Log::error('Error pada ambilDitahan TransaksiKasirController: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $e->getMessage(),
                ])
                ->log('gagal mengambil data pesanan ditahan');
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal mengambil data pesanan ditahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pulihkanDitahan($id)
    {
        DB::beginTransaction();
        try {
            $pesanan = PesananDitahan::with('transaksi')->findOrFail($id);

            // Kembalikan stok jika ada transaksi terkait, HANYA untuk baris
            // yang bukan "Perlu Diproduksi" (pakai snapshot butuh_produksi
            // yang disimpan saat transaksi dibuat, bukan status produk saat ini).
            if ($pesanan->transaksi) {
                $detailTransaksi = DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->get();
                $this->kembalikanStokDariDetail($detailTransaksi);

                // Hapus detail transaksi dan transaksi
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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pada pulihkanDitahan TransaksiKasirController: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $e->getMessage(),
                    'pesanan_id' => $id,
                ])
                ->log('gagal memulihkan pesanan ditahan');
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal memulihkan pesanan ditahan: ' . $e->getMessage()
            ], 500);
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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pada hapusDitahan TransaksiKasirController: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $e->getMessage(),
                    'pesanan_id' => $id,
                ])
                ->log('gagal menghapus pesanan ditahan');
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal menghapus pesanan ditahan: ' . $e->getMessage()
            ], 500);
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
                'diskon_label' => 'nullable|string|max:255',
            ]);

            $transaksi = $pesanan->transaksi;

            // Update transaksi menjadi Lunas
            $transaksi->update([
                'jumlah_diterima' => $validated['jumlah_diterima'],
                'kembalian' => $validated['kembalian'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'member_id' => $validated['member_id'] ?? $transaksi->member_id,
                'status_pembayaran' => 'Lunas',
                'diskon_label' => $validated['diskon_label'] ?? $transaksi->diskon_label,
            ]);

            // Hitung poin member
            if (!empty($validated['member_id'])) {
                $nominalUntukPoin = $transaksi->subtotal + $transaksi->pajak;
                if ($nominalUntukPoin >= 100000) {
                    $member = \App\Models\Member::find($validated['member_id']);
                    if ($member) {
                        $poinDiperoleh = floor($nominalUntukPoin / 100000) * 100;
                        $member->increment('poin', (int) $poinDiperoleh);
                    }
                }
            }

            // Hapus pesanan ditahan (karena sudah lunas)
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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pada lunaskanDitahan TransaksiKasirController: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $e->getMessage(),
                    'request_data' => $request->all(),
                ])
                ->log('gagal melunaskan pesanan');
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal melunaskan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bayar(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nama_pembeli' => 'nullable|string|max:255',
                'member_id' => 'nullable|exists:members,id',
                'item_pesanan' => 'required|array',
                'subtotal' => 'required|numeric',
                'pajak' => 'required|numeric',
                'diskon' => 'required|numeric',
                'diskon_label' => 'nullable|string|max:255',
                'total_bayar' => 'required|numeric',
                'jumlah_diterima' => 'required|numeric',
                'kembalian' => 'required|numeric',
                'metode_pembayaran' => 'required|string',
            ]);

            // Buat kode transaksi unik
            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_pembeli' => $validated['nama_pembeli'] ?? 'Pelanggan Umum',
                'member_id' => $validated['member_id'] ?? null,
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'diskon_label' => $request->diskon_label,
                'total_bayar' => $validated['total_bayar'],
                'jumlah_diterima' => $validated['jumlah_diterima'],
                'kembalian' => $validated['kembalian'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => 'Lunas',
            ]);

            //hitung poin member
            if ($transaksi->member_id) {
                $pengaturan = PengaturanMember::where('status_aktif', true)->first();

                if ($pengaturan) {
                    $nominalUntukPoin = $transaksi->subtotal + $transaksi->pajak;
                    $kelipatan = floor(
                        $nominalUntukPoin / $pengaturan->minimal_belanja
                    );
                    $poin = $kelipatan * $pengaturan->poin;
                    if ($poin > 0) {
                        $member = Member::find($transaksi->member_id);
                        if ($member) {
                            $member->increment('poin', $poin);
                        }
                    }
                }
            }

            foreach ($validated['item_pesanan'] as $item) {

                // ==== Item BUNDLING ====
                if (!empty($item['is_bundling']) && !empty($item['bundling_id'])) {
                    $this->prosesItemBundlingTransaksi($transaksi, $item);
                    continue;
                }

                // ==== Item PRODUK biasa (logic lama, tidak berubah) ====
                $produk = Produk::findOrFail($item['produk_id']);
                $perluProduksi = $this->tentukanPerluProduksi($produk, $item);

                if (!$perluProduksi && $produk->stok < $item['jumlah']) {
                    throw new \Exception("Stok untuk produk '{$produk->nama_produk}' tidak mencukupi (Tersedia: {$produk->stok}, Diminta: {$item['jumlah']})");
                }

                if (!$perluProduksi) {
                    $produk->decrement('stok', $item['jumlah']);
                }

                $detail = DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pada bayar TransaksiKasirController: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $e->getMessage(),
                    'request_data' => $request->all(),
                ])
                ->log('gagal memproses transaksi');
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
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
        } catch (\Exception $e) {
            Log::error('Error pada cetak TransaksiKasirController: ' . $e->getMessage());
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'error' => $e->getMessage(),
                    'kode_transaksi' => $kodeTransaksi,
                ])
                ->log('gagal mencetak struk transaksi');
            abort(404, 'Transaksi tidak ditemukan.');
        }
    }
}