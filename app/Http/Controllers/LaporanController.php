<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Histori Transaksi - daftar semua transaksi yang pernah dilakukan
     */
    public function historiTransaksi(Request $request)
    {
        $query = Transaksi::with('detailTransaksi')->orderBy('created_at', 'desc');

        // Filter berdasarkan pencarian
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('kode_transaksi', 'like', "%{$cari}%")
                  ->orWhere('nama_pembeli', 'like', "%{$cari}%");
            });
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('created_at', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_selesai);
        }

        $transaksiList = $query->paginate(15)->withQueryString();

        return view('pages.laporan.histori-transaksi', [
            'title' => 'Histori Transaksi',
            'transaksiList' => $transaksiList,
        ]);
    }

    /**
     * Detail transaksi - JSON API untuk modal detail
     */
    public function detailTransaksi($id)
    {
        $transaksi = Transaksi::with('detailTransaksi')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $transaksi,
        ]);
    }

    /**
     * Laporan Penjualan - ringkasan penjualan per minggu, bulan, tahun
     */
    public function laporanPenjualan(Request $request)
    {
        $periode = $request->get('periode', 'bulan'); // minggu, bulan, tahun
        $tahun = (int) $request->get('tahun', date('Y'));

        // Statistik ringkasan
        $hariIni = Carbon::today();
        $totalHariIni = Transaksi::whereDate('created_at', $hariIni)->sum('total_bayar');
        $jumlahTransaksiHariIni = Transaksi::whereDate('created_at', $hariIni)->count();

        $totalBulanIni = Transaksi::whereMonth('created_at', $hariIni->month)
            ->whereYear('created_at', $hariIni->year)
            ->sum('total_bayar');
        $jumlahTransaksiBulanIni = Transaksi::whereMonth('created_at', $hariIni->month)
            ->whereYear('created_at', $hariIni->year)
            ->count();

        $totalTahunIni = Transaksi::whereYear('created_at', $hariIni->year)->sum('total_bayar');
        $jumlahTransaksiTahunIni = Transaksi::whereYear('created_at', $hariIni->year)->count();

        // Data grafik berdasarkan periode
        $dataGrafik = [];
        $labelGrafik = [];

        if ($periode === 'minggu') {
            // 7 hari terakhir berdasarkan tahun terpilih (tanggal dan bulan saat ini, tahun disesuaikan)
            $tanggalAcuan = Carbon::now()->year($tahun);
            // Jika tahun terpilih adalah tahun saat ini, gunakan waktu sekarang asli
            if ($tahun == date('Y')) {
                $tanggalAcuan = Carbon::now();
            }

            for ($i = 6; $i >= 0; $i--) {
                $tanggal = (clone $tanggalAcuan)->subDays($i);
                $labelGrafik[] = $tanggal->translatedFormat('D, d M');
                $dataGrafik[] = (int) Transaksi::whereDate('created_at', $tanggal)->sum('total_bayar');
            }
        } elseif ($periode === 'bulan') {
            // 12 bulan di tahun yang dipilih
            $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($i = 1; $i <= 12; $i++) {
                $labelGrafik[] = $namaBulan[$i - 1];
                $dataGrafik[] = (int) Transaksi::whereMonth('created_at', $i)
                    ->whereYear('created_at', $tahun)
                    ->sum('total_bayar');
            }
        } elseif ($periode === 'tahun') {
            // 5 tahun terakhir sampai tahun terpilih
            for ($i = 4; $i >= 0; $i--) {
                $thn = (int) $tahun - $i;
                $labelGrafik[] = (string) $thn;
                $dataGrafik[] = (int) Transaksi::whereYear('created_at', $thn)->sum('total_bayar');
            }
        }

        // Produk terlaris (top 10)
        $produkTerlaris = DetailTransaksi::select('nama_produk', DB::raw('SUM(jumlah) as total_terjual'), DB::raw('SUM(subtotal) as total_pendapatan'))
            ->groupBy('nama_produk')
            ->orderByDesc('total_terjual')
            ->limit(10)
            ->get();

        // Daftar tahun yang memiliki transaksi
        $daftarTahun = Transaksi::selectRaw('YEAR(created_at) as tahun')
            ->groupBy('tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();

        if (empty($daftarTahun)) {
            $daftarTahun = [(int) date('Y')];
        }

        $title = $request->is('/') ? 'Beranda Dashboard' : 'Laporan Penjualan';

        return view('pages.laporan.laporan-penjualan', [
            'title' => $title,
            'periode' => $periode,
            'tahun' => $tahun,
            'totalHariIni' => $totalHariIni,
            'jumlahTransaksiHariIni' => $jumlahTransaksiHariIni,
            'totalBulanIni' => $totalBulanIni,
            'jumlahTransaksiBulanIni' => $jumlahTransaksiBulanIni,
            'totalTahunIni' => $totalTahunIni,
            'jumlahTransaksiTahunIni' => $jumlahTransaksiTahunIni,
            'dataGrafik' => $dataGrafik,
            'labelGrafik' => $labelGrafik,
            'produkTerlaris' => $produkTerlaris,
            'daftarTahun' => $daftarTahun,
        ]);
    }
}
