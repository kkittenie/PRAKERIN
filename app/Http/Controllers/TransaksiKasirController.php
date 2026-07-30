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

class TransaksiKasirController extends Controller
{
    /**
     * Tentukan apakah sebuah PRODUK (secara keseluruhan) berstatus
     * "Perlu Diproduksi". Dipakai untuk kondisi manual/legacy.
     *
     * Sebuah produk dianggap "Perlu Diproduksi" jika:
     * 1) admin menandainya manual sebagai 'perlu_diproduksi', ATAU
     * 2) stoknya sudah habis (<= 0).
     */
    private function butuhProduksi(Produk $produk): bool
    {
        return $produk->status_ketersediaan === 'perlu_diproduksi' || (float) $produk->stok <= 0;
    }

    /**
     * Tentukan status "Perlu Diproduksi" untuk SATU BARIS item pesanan.
     *
     * Kasir di frontend boleh menambahkan jumlah melebihi stok yang
     * tersedia. Kalau begitu, kasir akan MEMECAH permintaan itu menjadi
     * 2 baris keranjang untuk produk yang sama: sebagian "Ready Stock"
     * (sejumlah stok yang tersisa) dan sisanya "Perlu Diproduksi". Setiap
     * baris keranjang sudah membawa flag `butuh_produksi` miliknya sendiri.
     *
     * Aturan:
     * - Jika produk ditandai MANUAL sebagai 'perlu_diproduksi' oleh admin,
     *   maka SELURUH baris untuk produk ini otomatis "Perlu Diproduksi"
     *   (tidak displit), karena penandaan manual berlaku untuk keseluruhan
     *   produk, bukan sebagian.
     * - Selain itu (mode otomatis berbasis stok), backend memakai flag
     *   `butuh_produksi` yang sudah dihitung & displit oleh kasir di
     *   frontend untuk baris ini. Kalau flag tidak dikirim (klien lama),
     *   fallback ke pengecekan stok produk saat ini.
     */
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
            
            $daftarDitahan = PesananDitahan::with('transaksi')->orderBy('created_at', 'desc')->get();

            $daftarMember = \App\Models\Member::orderBy('nama')->get();

            return view('pages.transaksi.indeks', [
                'title' => 'Transaksi Kasir',
                'daftarProduk' => $daftarProduk,
                'daftarKategori' => $daftarKategori,
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
                'total_bayar' => $validated['total'],
                'jumlah_diterima' => 0,
                'kembalian' => 0,
                'metode_pembayaran' => 'Belum Bayar',
                'status_pembayaran' => 'Belum Lunas',
            ]);

            // Buat detail transaksi. Stok HANYA dikurangi untuk baris yang
            // BUKAN "Perlu Diproduksi". Satu produk bisa muncul sebagai 2
            // baris berbeda (Ready Stock & Perlu Diproduksi) kalau kasir
            // meminta jumlah melebihi stok yang tersedia.
            foreach ($validated['item_pesanan'] as $item) {
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

                // Set langsung via assignment (bukan mass-assignment) supaya
                // TIDAK ke-drop walau 'butuh_produksi' belum ada di $fillable
                // model DetailTransaksi.
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
                ->log('menahan pesanan (kredit)');

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

            // Kembalikan stok jika ada transaksi terkait, HANYA untuk item
            // yang bukan "Perlu Diproduksi" (pakai snapshot butuh_produksi
            // yang disimpan saat transaksi dibuat, bukan status produk saat ini).
            if ($pesanan->transaksi) {
                $detailTransaksi = DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->get();
                foreach ($detailTransaksi as $detail) {
                    if (!$detail->butuh_produksi) {
                        $produk = Produk::find($detail->produk_id);
                        if ($produk) {
                            $produk->increment('stok', $detail->jumlah);
                        }
                    }
                }

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

            // Kembalikan stok jika ada transaksi terkait, HANYA untuk item
            // yang bukan "Perlu Diproduksi".
            if ($pesanan->transaksi) {
                $detailTransaksi = DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->get();
                foreach ($detailTransaksi as $detail) {
                    if (!$detail->butuh_produksi) {
                        $produk = Produk::find($detail->produk_id);
                        if ($produk) {
                            $produk->increment('stok', $detail->jumlah);
                        }
                    }
                }

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

    /**
     * Lunaskan pesanan yang ditahan (proses pembayaran kredit).
     *
     * Catatan: stok sudah dikurangi (untuk item yang bukan "Perlu
     * Diproduksi") saat pesanan pertama kali ditahan di simpanDitahan(),
     * jadi di sini tidak ada perubahan stok lagi.
     */
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
            ]);

            $transaksi = $pesanan->transaksi;

            // Update transaksi menjadi Lunas
            $transaksi->update([
                'jumlah_diterima' => $validated['jumlah_diterima'],
                'kembalian' => $validated['kembalian'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'member_id' => $validated['member_id'] ?? $transaksi->member_id,
                'status_pembayaran' => 'Lunas',
            ]);

            // Hitung poin member
            if (!empty($validated['member_id']) && $transaksi->total_bayar >= 100000) {
                $member = \App\Models\Member::find($validated['member_id']);
                if ($member) {
                    $poinDiperoleh = floor($transaksi->total_bayar / 100000) * 100;
                    $member->increment('poin', (int) $poinDiperoleh);
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
                ->log('melunaskan pesanan kredit');

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
                ->log('gagal melunaskan pesanan kredit');
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
                    $kelipatan = floor(
                        $transaksi->total_bayar / $pengaturan->minimal_belanja
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

            // Buat detail transaksi. Stok HANYA dikurangi untuk baris yang
            // BUKAN "Perlu Diproduksi". Satu produk bisa muncul sebagai 2
            // baris berbeda (Ready Stock & Perlu Diproduksi) kalau kasir
            // meminta jumlah melebihi stok yang tersedia - baris "Perlu
            // Diproduksi" tetap boleh dibeli tanpa batas stok karena akan
            // dibuatkan oleh bagian produksi.
            foreach ($validated['item_pesanan'] as $item) {
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

                // Set langsung via assignment (bukan mass-assignment) supaya
                // TIDAK ke-drop walau 'butuh_produksi' belum ada di $fillable
                // model DetailTransaksi.
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