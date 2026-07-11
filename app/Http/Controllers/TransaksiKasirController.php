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

class TransaksiKasirController extends Controller
{
    /**
     * Menampilkan halaman utama transaksi kasir (POS)
     */
    public function indeks()
    {
        try {
            $daftarProduk = Produk::with('kategori')
                ->where('status_aktif', true)
                ->get();

            $daftarKategori = KategoriProduk::all();
            
            // Ambil daftar pesanan yang sedang ditahan untuk dipulihkan jika perlu
            $daftarDitahan = PesananDitahan::orderBy('created_at', 'desc')->get();

            return view('pages.transaksi.indeks', [
                'title' => 'Transaksi Kasir',
                'daftarProduk' => $daftarProduk,
                'daftarKategori' => $daftarKategori,
                'daftarDitahan' => $daftarDitahan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error pada indeks TransaksiKasirController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan transaksi aktif ke daftar pesanan ditahan (Hold Order)
     */
    public function simpanDitahan(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_pembeli' => 'nullable|string|max:255',
                'email_pembeli' => 'nullable|email|max:255',
                'item_pesanan' => 'required|array',
                'subtotal' => 'required|numeric',
                'pajak' => 'required|numeric',
                'diskon' => 'required|numeric',
                'total' => 'required|numeric',
                'catatan' => 'nullable|string',
            ]);

            $namaPembeli = $validated['nama_pembeli'] ?? 'Pelanggan Umum';
            
            $pesanan = PesananDitahan::create([
                'nama_pembeli' => $namaPembeli,
                'email_pembeli' => $validated['email_pembeli'],
                'item_pesanan' => $validated['item_pesanan'],
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'total' => $validated['total'],
                'catatan' => $validated['catatan'],
            ]);

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan berhasil ditahan.',
                'data' => $pesanan
            ]);
        } catch (\Exception $e) {
            Log::error('Error pada simpanDitahan TransaksiKasirController: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal menahan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil seluruh daftar pesanan ditahan (Hold Orders)
     */
    public function ambilDitahan()
    {
        try {
            $daftarDitahan = PesananDitahan::orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $daftarDitahan
            ]);
        } catch (\Exception $e) {
            Log::error('Error pada ambilDitahan TransaksiKasirController: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal mengambil data pesanan ditahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memulihkan / mengambil pesanan ditahan untuk diaktifkan kembali di kasir
     */
    public function pulihkanDitahan($id)
    {
        try {
            $pesanan = PesananDitahan::findOrFail($id);
            
            // Hapus dari tabel ditahan karena akan diproses kembali di kasir
            $pesanan->delete();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan berhasil dipulihkan.',
                'data' => $pesanan
            ]);
        } catch (\Exception $e) {
            Log::error('Error pada pulihkanDitahan TransaksiKasirController: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal memulihkan pesanan ditahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus pesanan ditahan dari daftar
     */
    public function hapusDitahan($id)
    {
        try {
            $pesanan = PesananDitahan::findOrFail($id);
            $pesanan->delete();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan ditahan berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error pada hapusDitahan TransaksiKasirController: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal menghapus pesanan ditahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memproses pembayaran (menyimpan transaksi final, mengurangi stok produk)
     */
    public function bayar(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nama_pembeli' => 'nullable|string|max:255',
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

            // Simpan transaksi utama
            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_pembeli' => $validated['nama_pembeli'] ?? 'Pelanggan Umum',
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'total_bayar' => $validated['total_bayar'],
                'jumlah_diterima' => $validated['jumlah_diterima'],
                'kembalian' => $validated['kembalian'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
            ]);

            // Simpan detail transaksi dan kurangi stok
            foreach ($validated['item_pesanan'] as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                
                // Cek ketersediaan stok
                if ($produk->stok < $item['jumlah']) {
                    throw new \Exception("Stok untuk produk '{$produk->nama_produk}' tidak mencukupi (Tersedia: {$produk->stok}, Diminta: {$item['jumlah']})");
                }

                // Kurangi stok produk
                $produk->decrement('stok', $item['jumlah']);

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'harga_satuan' => $item['harga_satuan'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['harga_satuan'] * $item['jumlah'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Transaksi berhasil diselesaikan.',
                'kode_transaksi' => $kodeTransaksi,
                'data' => $transaksi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pada bayar TransaksiKasirController: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
