<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Support\ActivityLogHelper;
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
        // Query dasar + filter tanggal/status dipakai baik untuk render halaman
        // maupun untuk request AJAX DataTables (route yang sama).
        $buildQuery = function () use ($request) {
            $query = Transaksi::query();

            // Filter berdasarkan tanggal
            if ($request->filled('tanggal_mulai')) {
                $query->whereDate('created_at', '>=', $request->tanggal_mulai);
            }
            if ($request->filled('tanggal_selesai')) {
                $tglSelesai = $request->tanggal_selesai;
                if ($request->filled('tanggal_mulai') && $tglSelesai < $request->tanggal_mulai) {
                    $tglSelesai = $request->tanggal_mulai;
                }
                $query->whereDate('created_at', '<=', $tglSelesai);
            }

            // Filter berdasarkan status pembayaran
            if ($request->filled('status') && $request->status !== 'semua') {
                $status = strtolower($request->status);
                if ($status === 'lunas') {
                    $query->whereRaw('LOWER(status_pembayaran) = ?', ['lunas']);
                } elseif (in_array($status, ['belum_bayar', 'belum_lunas'])) {
                    $query->whereRaw('LOWER(status_pembayaran) LIKE ?', ['%belum%']);
                }
            }

            return $query;
        };

        if ($request->ajax()) {
            try {
                $query = $buildQuery();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addColumn('kode_transaksi_html', fn($item) => view('pages.laporan.partials.kode-transaksi', compact('item'))->render())
                    ->editColumn('created_at', fn($item) => $item->created_at->format('d M Y, H:i'))
                    ->addColumn('status_html', fn($item) => view('pages.laporan.partials.status-pembayaran', compact('item'))->render())
                    ->editColumn('subtotal', fn($item) => 'Rp ' . number_format($item->subtotal, 0, ',', '.'))
                    ->editColumn('diskon', fn($item) => 'Rp ' . number_format($item->diskon, 0, ',', '.'))
                    ->editColumn('total_bayar', fn($item) => 'Rp ' . number_format($item->total_bayar, 0, ',', '.'))
                    ->addColumn('metode_html', fn($item) => view('pages.laporan.partials.metode-pembayaran', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.laporan.partials.aksi-histori', compact('item'))->render())
                    ->orderColumn('kode_transaksi_html', function ($query, $order) {
                        $query->orderBy('kode_transaksi', $order);
                    })
                    ->orderColumn('status_html', function ($query, $order) {
                        $query->orderBy('status_pembayaran', $order);
                    })
                    ->orderColumn('metode_html', function ($query, $order) {
                        $query->orderBy('metode_pembayaran', $order);
                    })
                    ->filter(function ($query) use ($request) {
                        // Override pencarian global supaya tetap bisa cari lewat
                        // kode transaksi & nama pembeli (seperti sebelumnya).
                        $search = $request->input('search.value');

                        if ($search) {
                            $query->where(function ($q) use ($search) {
                                $q->where('kode_transaksi', 'like', "%{$search}%")
                                    ->orWhere('nama_pembeli', 'like', "%{$search}%");
                            });
                        }
                    }, true)
                    ->rawColumns(['kode_transaksi_html', 'status_html', 'metode_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat histori transaksi.'], 500);
            }
        }

        return view('pages.laporan.histori-transaksi', [
            'title' => 'Histori Transaksi',
        ]);
    }

    /**
     * Detail transaksi - JSON API untuk modal detail
     */
    public function detailTransaksi($id)
    {
        try {
            $transaksi = Transaksi::with('detailTransaksi.produk')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $transaksi,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Laporan Penjualan - ringkasan penjualan per minggu, bulan, tahun
     */
    public function laporanPenjualan(Request $request)
    {
        if ($request->ajax()) {
            try {
                // Produk terlaris (top 10 sepanjang waktu) - hasil agregasi GROUP BY.
                // Diambil sebagai collection dulu (bukan query builder) supaya urutan
                // "top 10 by total_terjual" ini tetap jadi seleksi datanya, dan sort
                // dari DataTables (klik header kolom lain) beneran menggantikan
                // urutan tampil tanpa terbentur ->orderByDesc() bawaan di atas.
                $produkTerlaris = DetailTransaksi::select('nama_produk', DB::raw('SUM(jumlah) as total_terjual'), DB::raw('SUM(subtotal) as total_pendapatan'))
                    ->groupBy('nama_produk')
                    ->orderByDesc('total_terjual')
                    ->limit(10)
                    ->get();

                return \Yajra\DataTables\Facades\DataTables::of($produkTerlaris)
                    ->addIndexColumn()
                    ->addColumn('total_terjual_html', fn($item) => view('pages.laporan.partials.total-terjual', compact('item'))->render())
                    ->addColumn('total_pendapatan_html', fn($item) => view('pages.laporan.partials.total-pendapatan', compact('item'))->render())
                    ->rawColumns(['total_terjual_html', 'total_pendapatan_html'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data produk terlaris.'], 500);
            }
        }

        try {
            $periode = $request->get('periode', 'bulan'); // minggu, bulan, tahun
            $tahun = (int) $request->get('tahun', date('Y'));

            // Statistik ringkasan
            $hariIni = Carbon::today();
            $totalHariIni = Transaksi::where('status_pembayaran', 'Lunas')->whereDate('created_at', $hariIni)->sum('total_bayar');
            $jumlahTransaksiHariIni = Transaksi::where('status_pembayaran', 'Lunas')->whereDate('created_at', $hariIni)->count();

            $totalBulanIni = Transaksi::where('status_pembayaran', 'Lunas')->whereMonth('created_at', $hariIni->month)
                ->whereYear('created_at', $hariIni->year)
                ->sum('total_bayar');
            $jumlahTransaksiBulanIni = Transaksi::where('status_pembayaran', 'Lunas')->whereMonth('created_at', $hariIni->month)
                ->whereYear('created_at', $hariIni->year)
                ->count();

            $totalTahunIni = Transaksi::where('status_pembayaran', 'Lunas')->whereYear('created_at', $hariIni->year)->sum('total_bayar');
            $jumlahTransaksiTahunIni = Transaksi::where('status_pembayaran', 'Lunas')->whereYear('created_at', $hariIni->year)->count();

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
                    $dataGrafik[] = (int) Transaksi::where('status_pembayaran', 'Lunas')->whereDate('created_at', $tanggal)->sum('total_bayar');
                }
            } elseif ($periode === 'bulan') {
                // 12 bulan di tahun yang dipilih
                $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                for ($i = 1; $i <= 12; $i++) {
                    $labelGrafik[] = $namaBulan[$i - 1];
                    $dataGrafik[] = (int) Transaksi::where('status_pembayaran', 'Lunas')->whereMonth('created_at', $i)
                        ->whereYear('created_at', $tahun)
                        ->sum('total_bayar');
                }
            } elseif ($periode === 'tahun') {
                // 5 tahun terakhir sampai tahun terpilih
                for ($i = 4; $i >= 0; $i--) {
                    $thn = (int) $tahun - $i;
                    $labelGrafik[] = (string) $thn;
                    $dataGrafik[] = (int) Transaksi::where('status_pembayaran', 'Lunas')->whereYear('created_at', $thn)->sum('total_bayar');
                }
            }

            // Daftar tahun yang memiliki transaksi
            $daftarTahun = Transaksi::selectRaw('YEAR(created_at) as tahun')
                ->groupBy('tahun')
                ->orderByDesc('tahun')
                ->pluck('tahun')
                ->toArray();

            if (empty($daftarTahun)) {
                $daftarTahun = [(int) date('Y')];
            }

            $title = 'Laporan Penjualan';

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
                'daftarTahun' => $daftarTahun,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}