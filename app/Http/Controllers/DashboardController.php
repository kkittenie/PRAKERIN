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
            $user = auth()->user();
            if ($user && !$user->can('lihat_dashboard')) {
                foreach (\App\Helpers\MenuHelper::getMainNavItems() as $group) {
                    foreach ($group['subItems'] as $sub) {
                        $perms = (array) ($sub['permission'] ?? []);
                        foreach ($perms as $p) {
                            if ($user->can($p)) {
                                return redirect($sub['path']);
                            }
                        }
                    }
                }
                abort(403, 'Akun Anda tidak memiliki hak akses ke halaman dashboard.');
            }

            $hariIni = Carbon::today();
            $bulanIni = $hariIni->month;
            $tahunIni = $hariIni->year;

            // ===== METRICS CARDS =====
            $pendapatanHariIni = (int) Transaksi::penjualanSah()->whereDate('created_at', $hariIni)->sum(DB::raw('total_bayar - COALESCE(pajak, 0)'));
            $transaksiHariIni = Transaksi::penjualanSah()->whereDate('created_at', $hariIni)->count();

            $pendapatanBulanIni = (int) Transaksi::penjualanSah()->whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->sum(DB::raw('total_bayar - COALESCE(pajak, 0)'));
            $transaksiBulanIni = Transaksi::penjualanSah()->whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->count();

            // Pelanggan unik bulan ini (berdasarkan nama_pembeli)
            $pelangganBulanIni = Transaksi::penjualanSah()->whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->distinct('nama_pembeli')
                ->count('nama_pembeli');

            // Rata-rata per transaksi bulan ini
            $rataRataTransaksi = $transaksiBulanIni > 0
                ? (int) ($pendapatanBulanIni / $transaksiBulanIni)
                : 0;

            // Perbandingan dengan bulan lalu
            $bulanLalu = Carbon::today()->subMonth();
            $pendapatanBulanLalu = (int) Transaksi::penjualanSah()->whereMonth('created_at', $bulanLalu->month)
                ->whereYear('created_at', $bulanLalu->year)
                ->sum(DB::raw('total_bayar - COALESCE(pajak, 0)'));
            $transaksiBulanLalu = Transaksi::penjualanSah()->whereMonth('created_at', $bulanLalu->month)
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
                $penjualanBulanan[] = (int) Transaksi::penjualanSah()->whereMonth('created_at', $i)
                    ->whereYear('created_at', $tahunIni)
                    ->sum(DB::raw('total_bayar - COALESCE(pajak, 0)'));
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
                $dataTransaksiHarian[] = Transaksi::penjualanSah()->whereDate('created_at', $tanggal)->count();
                $dataPendapatanHarian[] = (int) Transaksi::penjualanSah()->whereDate('created_at', $tanggal)->sum(DB::raw('total_bayar - COALESCE(pajak, 0)'));
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

            // ===== PESANAN MOBILE TERBARU (5 terakhir yang masih diproses & belum lunas, tampil di bawah peringatan stok) =====
            $pesananMobileTerbaru = Transaksi::with('member')
                ->where('asal_pesanan', Transaksi::ASAL_MEMBER_APP)
                // Dashboard hanya menampilkan pesanan yang masih diproses dan belum lunas;
                // yang dibatalkan, sudah diambil, atau sudah lunas tidak ditampilkan.
                ->where('status_pesanan', Transaksi::STATUS_DIPROSES)
                ->where('status_pembayaran', '!=', 'Lunas')
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
                'pesananMobileTerbaru' => $pesananMobileTerbaru,
                'lowStockProduk' => $lowStockProduk,
                'lowStockBahan' => $lowStockBahan,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}