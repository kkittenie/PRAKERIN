<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $hariIni = Carbon::today();
            $bulanIni = $hariIni->month;
            $tahunIni = $hariIni->year;

            // ===== METRICS CARDS =====
            $pendapatanHariIni = (int) Transaksi::whereDate('created_at', $hariIni)->sum('total_bayar');
            $transaksiHariIni = Transaksi::whereDate('created_at', $hariIni)->count();

            $pendapatanBulanIni = (int) Transaksi::whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->sum('total_bayar');
            $transaksiBulanIni = Transaksi::whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->count();

            // Pelanggan unik bulan ini (berdasarkan nama_pembeli)
            $pelangganBulanIni = Transaksi::whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->distinct('nama_pembeli')
                ->count('nama_pembeli');

            // Rata-rata per transaksi bulan ini
            $rataRataTransaksi = $transaksiBulanIni > 0
                ? (int) ($pendapatanBulanIni / $transaksiBulanIni)
                : 0;

            // Perbandingan dengan bulan lalu
            $bulanLalu = Carbon::today()->subMonth();
            $pendapatanBulanLalu = (int) Transaksi::whereMonth('created_at', $bulanLalu->month)
                ->whereYear('created_at', $bulanLalu->year)
                ->sum('total_bayar');
            $transaksiBulanLalu = Transaksi::whereMonth('created_at', $bulanLalu->month)
                ->whereYear('created_at', $bulanLalu->year)
                ->count();

            // Persentase perubahan pendapatan
            $perubahanPendapatan = $pendapatanBulanLalu > 0
                ? round((($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100, 1)
                : ($pendapatanBulanIni > 0 ? 100 : 0);

            // Persentase perubahan transaksi
            $perubahanTransaksi = $transaksiBulanLalu > 0
                ? round((($transaksiBulanIni - $transaksiBulanLalu) / $transaksiBulanLalu) * 100, 1)
                : ($transaksiBulanIni > 0 ? 100 : 0);

            // ===== GRAFIK PENJUALAN BULANAN (Bar Chart) =====
            $penjualanBulanan = [];
            for ($i = 1; $i <= 12; $i++) {
                $penjualanBulanan[] = (int) Transaksi::whereMonth('created_at', $i)
                    ->whereYear('created_at', $tahunIni)
                    ->sum('total_bayar');
            }

            // ===== TARGET BULANAN (Gauge) =====
            // Persentase pencapaian: bulan ini vs bulan lalu
            $pencapaianTarget = $pendapatanBulanLalu > 0
                ? min(round(($pendapatanBulanIni / $pendapatanBulanLalu) * 100), 200)
                : ($pendapatanBulanIni > 0 ? 100 : 0);

            // ===== GRAFIK TREN HARIAN (Line Chart - 7 hari terakhir) =====
            $labelHarian = [];
            $dataTransaksiHarian = [];
            $dataPendapatanHarian = [];
            for ($i = 6; $i >= 0; $i--) {
                $tanggal = Carbon::today()->subDays($i);
                $labelHarian[] = $tanggal->translatedFormat('D, d M');
                $dataTransaksiHarian[] = Transaksi::whereDate('created_at', $tanggal)->count();
                $dataPendapatanHarian[] = (int) Transaksi::whereDate('created_at', $tanggal)->sum('total_bayar');
            }

            // ===== PRODUK TERLARIS (Top 5) =====
            $produkTerlaris = DetailTransaksi::select(
                    'nama_produk',
                    DB::raw('SUM(jumlah) as total_terjual'),
                    DB::raw('SUM(subtotal) as total_pendapatan')
                )
                ->groupBy('nama_produk')
                ->orderByDesc('total_terjual')
                ->limit(5)
                ->get();

            // ===== TRANSAKSI TERBARU (5 terakhir) =====
            $transaksiTerbaru = Transaksi::with('detailTransaksi')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // ===== NOTIFIKASI STOK MENIPIS =====
            $lowStockProduk = \App\Models\Produk::with('detailProduks')
                ->where('status_aktif', true)
                ->stokMenipis()
                ->get();
            $lowStockBahan = \App\Models\BahanBaku::whereColumn('stok', '<=', 'stok_minimum')
                ->get();

            return view('pages.dashboard.beranda', [
                'title' => 'Rekap Penjualan',
                'pendapatanHariIni' => $pendapatanHariIni,
                'transaksiHariIni' => $transaksiHariIni,
                'pendapatanBulanIni' => $pendapatanBulanIni,
                'transaksiBulanIni' => $transaksiBulanIni,
                'pelangganBulanIni' => $pelangganBulanIni,
                'rataRataTransaksi' => $rataRataTransaksi,
                'perubahanPendapatan' => $perubahanPendapatan,
                'perubahanTransaksi' => $perubahanTransaksi,
                'pendapatanBulanLalu' => $pendapatanBulanLalu,
                'penjualanBulanan' => $penjualanBulanan,
                'pencapaianTarget' => $pencapaianTarget,
                'labelHarian' => $labelHarian,
                'dataTransaksiHarian' => $dataTransaksiHarian,
                'dataPendapatanHarian' => $dataPendapatanHarian,
                'produkTerlaris' => $produkTerlaris,
                'transaksiTerbaru' => $transaksiTerbaru,
                'lowStockProduk' => $lowStockProduk,
                'lowStockBahan' => $lowStockBahan,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}