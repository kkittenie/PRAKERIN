<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Support\ActivityLogHelper;
use App\Support\JamOperasional;
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
     * Hitung laba bersih (pendapatan - harga modal) untuk transaksi lunas
     * dalam rentang waktu tertentu.
     *
     * @param  \Closure  $filterCallback  Callback untuk filter query (tanggal, dll)
     * @return array  ['pendapatan' => int, 'modal' => int, 'laba' => int, 'jumlah_transaksi' => int]
     */
    private function hitungLabaBersih(\Closure $filterCallback): array
    {
        // Hitung total pendapatan dan jumlah transaksi
        $queryTransaksi = Transaksi::penjualanSah();
        $filterCallback($queryTransaksi);

        // Pendapatan bersih: total_bayar sudah termasuk pajak (titipan), jadi pajak dikeluarkan
        $pendapatan = (int) (clone $queryTransaksi)->sum(DB::raw('total_bayar - COALESCE(pajak, 0)'));
        $jumlahTransaksi = (clone $queryTransaksi)->count();

        // Hitung total harga modal dari detail transaksi
        // LEFT JOIN ke detail_produks untuk mendapatkan harga_modal
        $queryModal = DB::table('detail_transaksi as dt')
            ->join('transaksi as t', 'dt.transaksi_id', '=', 't.id')
            ->leftJoin('detail_produks as dp', 'dt.detail_produk_id', '=', 'dp.id')
            // Produk Tunggal bervarian: modal diambil dari varian yang terjual (dicocokkan lewat kode di detail transaksi).
            ->leftJoin('varian_produks as vp', function ($join) {
                $join->on('vp.produk_id', '=', 'dt.produk_id')->on('vp.kode_produk', '=', 'dt.kode_barcode');
            })
            ->where('t.status_pembayaran', 'Lunas')
            ->where(function ($q) {
                $q->whereNull('t.status_pesanan')
                  ->orWhere('t.status_pesanan', '!=', Transaksi::STATUS_DIBATALKAN);
            });

        $filterCallback($queryModal, 't');

        $totalModal = (int) $queryModal->sum(DB::raw('COALESCE(vp.harga_modal, dp.harga_modal, 0) * dt.jumlah'));

        return [
            'pendapatan'       => $pendapatan,
            'modal'            => $totalModal,
            'laba'             => $pendapatan - $totalModal,
            'jumlah_transaksi' => $jumlahTransaksi,
        ];
    }

    /**
     * Laporan Penjualan - ringkasan penjualan per minggu, bulan, tahun
     */
    public function laporanPenjualan(Request $request)
    {
        if ($request->ajax()) {
            try {
                // Produk terlaris (top 10) dengan laba bersih.
                // Mendukung filter periode/tipe: 'hari', 'bulan', 'tahun' (default: 'hari').
                $tipeTerlaris = $request->input('tipe_terlaris', 'hari');

                // Baris isi bundling / promo event dicatat dengan subtotal 0 (uangnya ada di baris paket),
                // jadi kalau dihitung sebagai produk sendiri labanya jadi minus dan jumlah terjualnya
                // menggelembung. Karena itu: baris isi TIDAK tampil sendiri, tapi modalnya digabung ke
                // baris paketnya. Total modal tetap sama dengan kartu ringkasan.
                $barisIsiPaket = '(dt.produk_id IS NOT NULL AND (dt.bundling_id IS NOT NULL OR dt.promo_event_id IS NOT NULL))';

                // Helper closure untuk filter waktu pada subquery
                $terapkanFilterWaktu = function ($query) use ($request, $tipeTerlaris) {
                    if ($tipeTerlaris === 'bulan') {
                        $bulanInput = (string) $request->input('bulan_terlaris', date('Y-m'));
                        try {
                            $c = Carbon::createFromFormat('Y-m', $bulanInput);
                        } catch (\Throwable $e) {
                            $c = Carbon::today();
                        }
                        $query->whereMonth('t.created_at', $c->month)
                              ->whereYear('t.created_at', $c->year);
                    } elseif ($tipeTerlaris === 'tahun') {
                        $tahunInput = (int) $request->input('tahun_terlaris', date('Y'));
                        $query->whereYear('t.created_at', $tahunInput);
                    } else { // default 'hari'
                        $tanggalInput = (string) $request->input('tanggal_terlaris', $request->input('tanggal', date('Y-m-d')));
                        try {
                            $c = Carbon::parse($tanggalInput);
                        } catch (\Throwable $e) {
                            $c = Carbon::today();
                        }
                        $query->whereDate('t.created_at', $c->toDateString());
                    }
                };

                // 1) Baris yang tampil: produk biasa + baris paket (bukan isi paket)
                $barisTampil = DB::table('detail_transaksi as dt')
                    ->join('transaksi as t', 'dt.transaksi_id', '=', 't.id')
                    ->leftJoin('detail_produks as dp', 'dt.detail_produk_id', '=', 'dp.id')
                    // Produk Tunggal bervarian: modal diambil dari varian yang terjual (dicocokkan lewat kode di detail transaksi).
                    ->leftJoin('varian_produks as vp', function ($join) {
                        $join->on('vp.produk_id', '=', 'dt.produk_id')->on('vp.kode_produk', '=', 'dt.kode_barcode');
                    })
                    ->where('t.status_pembayaran', 'Lunas')
                    ->where(function ($q) {
                        $q->whereNull('t.status_pesanan')
                          ->orWhere('t.status_pesanan', '!=', Transaksi::STATUS_DIBATALKAN);
                    })
                    ->whereRaw("NOT {$barisIsiPaket}")
                    ->selectRaw('dt.nama_produk as label')
                    ->selectRaw('dt.jumlah as jumlah')
                    ->selectRaw('dt.subtotal as pendapatan')
                    ->selectRaw('COALESCE(vp.harga_modal, dp.harga_modal, 0) * dt.jumlah as modal');
                $terapkanFilterWaktu($barisTampil);

                // 2) Modal isi paket: dilabeli dengan nama baris paket di transaksi yang sama
                $modalIsiPaket = DB::table('detail_transaksi as dt')
                    ->join('transaksi as t', 'dt.transaksi_id', '=', 't.id')
                    ->leftJoin('detail_produks as dp', 'dt.detail_produk_id', '=', 'dp.id')
                    // Produk Tunggal bervarian: modal diambil dari varian yang terjual (dicocokkan lewat kode di detail transaksi).
                    ->leftJoin('varian_produks as vp', function ($join) {
                        $join->on('vp.produk_id', '=', 'dt.produk_id')->on('vp.kode_produk', '=', 'dt.kode_barcode');
                    })
                    ->where('t.status_pembayaran', 'Lunas')
                    ->where(function ($q) {
                        $q->whereNull('t.status_pesanan')
                          ->orWhere('t.status_pesanan', '!=', Transaksi::STATUS_DIBATALKAN);
                    })
                    ->whereRaw($barisIsiPaket)
                    ->selectRaw("COALESCE(
                            (SELECT MIN(h.nama_produk) FROM detail_transaksi h
                              WHERE h.transaksi_id = dt.transaksi_id AND h.produk_id IS NULL
                                AND h.bundling_id IS NOT NULL AND h.bundling_id = dt.bundling_id),
                            (SELECT MIN(h.nama_produk) FROM detail_transaksi h
                              WHERE h.transaksi_id = dt.transaksi_id AND h.produk_id IS NULL
                                AND h.promo_event_id IS NOT NULL AND h.promo_event_id = dt.promo_event_id),
                            dt.nama_produk
                        ) as label")
                    ->selectRaw('0 as jumlah')
                    ->selectRaw('0 as pendapatan')
                    ->selectRaw('COALESCE(vp.harga_modal, dp.harga_modal, 0) * dt.jumlah as modal');
                $terapkanFilterWaktu($modalIsiPaket);

                $produkTerlaris = DB::query()
                    ->fromSub($barisTampil->unionAll($modalIsiPaket), 'x')
                    ->select(
                        'label as nama_produk',
                        DB::raw('SUM(jumlah) as total_terjual'),
                        DB::raw('SUM(pendapatan) as total_pendapatan'),
                        DB::raw('SUM(modal) as total_modal'),
                        DB::raw('SUM(pendapatan) - SUM(modal) as laba_bersih')
                    )
                    ->groupBy('label')
                    ->havingRaw('SUM(jumlah) > 0')
                    ->orderByDesc('total_terjual')
                    ->limit(10)
                    ->get();

                return \Yajra\DataTables\Facades\DataTables::of($produkTerlaris)
                    ->addIndexColumn()
                    ->addColumn('total_terjual_html', fn($item) => view('pages.laporan.partials.total-terjual', compact('item'))->render())
                    ->addColumn('total_pendapatan_html', fn($item) => view('pages.laporan.partials.total-pendapatan', compact('item'))->render())
                    ->addColumn('laba_bersih_html', fn($item) => view('pages.laporan.partials.laba-bersih', compact('item'))->render())
                    ->rawColumns(['total_terjual_html', 'total_pendapatan_html', 'laba_bersih_html'])
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
            $periode = $request->get('periode', 'bulan'); // hari, minggu, bulan, tahun
            if (!in_array($periode, ['hari', 'minggu', 'bulan', 'tahun'], true)) {
                $periode = 'bulan';
            }
            $tahun = (int) $request->get('tahun', date('Y'));

            // Statistik ringkasan — laba bersih (pendapatan - modal)
            $hariIni = Carbon::today();

            // Filter "Hari": tanggal yang dipilih (default hari ini). Kartu pertama ikut tanggal ini.
            try {
                $tanggalDipilih = Carbon::createFromFormat('Y-m-d', (string) $request->get('tanggal', $hariIni->toDateString()))->startOfDay();
            } catch (\Throwable $e) {
                $tanggalDipilih = $hariIni->copy();
            }
            $tanggalStat = $periode === 'hari' ? $tanggalDipilih : $hariIni;

            // Filter "Bulan": bulan yang dipilih (default bulan ini). Kartu kedua ikut bulan ini jika mode 'bulan'.
            $bulanInput = (string) $request->get('bulan', $hariIni->format('Y-m'));
            try {
                $carbonBulan = Carbon::createFromFormat('Y-m', $bulanInput)->startOfMonth();
            } catch (\Throwable $e) {
                $carbonBulan = $hariIni->copy()->startOfMonth();
            }
            $bulanStat = $periode === 'bulan' ? $carbonBulan : $hariIni;

            $statHariIni = $this->hitungLabaBersih(function ($query, $alias = null) use ($tanggalStat) {
                $col = $alias ? "{$alias}.created_at" : 'created_at';
                $query->whereDate($col, $tanggalStat);
            });

            $statBulanIni = $this->hitungLabaBersih(function ($query, $alias = null) use ($bulanStat) {
                $col = $alias ? "{$alias}.created_at" : 'created_at';
                $query->whereMonth($col, $bulanStat->month)
                      ->whereYear($col, $bulanStat->year);
            });

            $tahunStat = $periode === 'tahun' ? $tahun : $hariIni->year;
            $statTahunIni = $this->hitungLabaBersih(function ($query, $alias = null) use ($tahunStat) {
                $col = $alias ? "{$alias}.created_at" : 'created_at';
                $query->whereYear($col, $tahunStat);
            });

            // Data grafik berdasarkan periode
            $dataGrafikPendapatan = [];
            $dataGrafikLaba = [];
            $labelGrafik = [];

            if ($periode === 'hari') {
                // Dipecah per jam mengikuti jam operasional toko (Pengaturan Jam Operasional).
                // Contoh buka 06:00 & tutup 20:00 -> kolom 06:00 sampai 19:00.
                $jamMulai = (int) explode(':', JamOperasional::buka())[0];
                [$jamTutup, $menitTutup] = array_map('intval', explode(':', JamOperasional::tutup()));
                $jamAkhir = $menitTutup > 0 ? $jamTutup : $jamTutup - 1;

                // Jam operasional lewat tengah malam / tidak masuk akal: tampilkan sehari penuh
                if ($jamAkhir < $jamMulai) {
                    $jamMulai = 0;
                    $jamAkhir = 23;
                }

                // Kalau ada transaksi di luar jam operasional (mis. penjualan langsung), jamnya tetap ditampilkan
                $rentangJam = Transaksi::penjualanSah()
                    ->whereDate('created_at', $tanggalDipilih)
                    ->selectRaw('MIN(HOUR(created_at)) as jam_min, MAX(HOUR(created_at)) as jam_max')
                    ->first();
                if ($rentangJam && $rentangJam->jam_min !== null) {
                    $jamMulai = min($jamMulai, (int) $rentangJam->jam_min);
                    $jamAkhir = max($jamAkhir, (int) $rentangJam->jam_max);
                }
                $jamMulai = max(0, $jamMulai);
                $jamAkhir = min(23, $jamAkhir);

                for ($jam = $jamMulai; $jam <= $jamAkhir; $jam++) {
                    $labelGrafik[] = sprintf('%02d:00', $jam);

                    $stat = $this->hitungLabaBersih(function ($query, $alias = null) use ($tanggalDipilih, $jam) {
                        $col = $alias ? "{$alias}.created_at" : 'created_at';
                        $query->whereDate($col, $tanggalDipilih)
                              ->whereRaw("HOUR({$col}) = ?", [$jam]);
                    });
                    $dataGrafikPendapatan[] = $stat['pendapatan'];
                    $dataGrafikLaba[] = $stat['laba'];
                }
            } elseif ($periode === 'minggu') {
                // 7 hari terakhir berdasarkan tahun terpilih
                $tanggalAcuan = Carbon::now()->year($tahun);
                if ($tahun == date('Y')) {
                    $tanggalAcuan = Carbon::now();
                }

                for ($i = 6; $i >= 0; $i--) {
                    $tanggal = (clone $tanggalAcuan)->subDays($i);
                    $labelGrafik[] = $tanggal->translatedFormat('D, d M');

                    $stat = $this->hitungLabaBersih(function ($query, $alias = null) use ($tanggal) {
                        $col = $alias ? "{$alias}.created_at" : 'created_at';
                        $query->whereDate($col, $tanggal);
                    });
                    $dataGrafikPendapatan[] = $stat['pendapatan'];
                    $dataGrafikLaba[] = $stat['laba'];
                }
            } elseif ($periode === 'bulan') {
                // Hari-hari dalam bulan yang dipilih (tanggal 1 s/d akhir bulan)
                $jumlahHari = $carbonBulan->daysInMonth;
                for ($hari = 1; $hari <= $jumlahHari; $hari++) {
                    $tanggalHari = (clone $carbonBulan)->day($hari);
                    $labelGrafik[] = sprintf('%02d', $hari);

                    $stat = $this->hitungLabaBersih(function ($query, $alias = null) use ($tanggalHari) {
                        $col = $alias ? "{$alias}.created_at" : 'created_at';
                        $query->whereDate($col, $tanggalHari->toDateString());
                    });
                    $dataGrafikPendapatan[] = $stat['pendapatan'];
                    $dataGrafikLaba[] = $stat['laba'];
                }
            } elseif ($periode === 'tahun') {
                // 12 bulan di tahun yang dipilih
                $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                for ($i = 1; $i <= 12; $i++) {
                    $labelGrafik[] = $namaBulan[$i - 1];

                    $bulan = $i;
                    $stat = $this->hitungLabaBersih(function ($query, $alias = null) use ($bulan, $tahun) {
                        $col = $alias ? "{$alias}.created_at" : 'created_at';
                        $query->whereMonth($col, $bulan)
                              ->whereYear($col, $tahun);
                    });
                    $dataGrafikPendapatan[] = $stat['pendapatan'];
                    $dataGrafikLaba[] = $stat['laba'];
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
                'title'          => $title,
                'periode'        => $periode,
                'tahun'          => $tahun,
                'tanggal'        => $tanggalDipilih->toDateString(),
                'labelTanggal'   => $tanggalDipilih->translatedFormat('d M Y'),
                'bulan'          => $carbonBulan->format('Y-m'),
                'labelBulan'     => $carbonBulan->translatedFormat('F Y'),
                // Stat cards — laba bersih
                'labaHariIni'              => $statHariIni['laba'],
                'pendapatanHariIni'        => $statHariIni['pendapatan'],
                'modalHariIni'             => $statHariIni['modal'],
                'jumlahTransaksiHariIni'   => $statHariIni['jumlah_transaksi'],
                'labaBulanIni'             => $statBulanIni['laba'],
                'pendapatanBulanIni'       => $statBulanIni['pendapatan'],
                'modalBulanIni'            => $statBulanIni['modal'],
                'jumlahTransaksiBulanIni'  => $statBulanIni['jumlah_transaksi'],
                'labaTahunIni'             => $statTahunIni['laba'],
                'pendapatanTahunIni'       => $statTahunIni['pendapatan'],
                'modalTahunIni'            => $statTahunIni['modal'],
                'jumlahTransaksiTahunIni'  => $statTahunIni['jumlah_transaksi'],
                // Grafik
                'dataGrafikPendapatan' => $dataGrafikPendapatan,
                'dataGrafikLaba'       => $dataGrafikLaba,
                'labelGrafik'          => $labelGrafik,
                'daftarTahun'          => $daftarTahun,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}