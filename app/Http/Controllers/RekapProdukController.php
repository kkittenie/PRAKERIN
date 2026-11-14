<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Support\ActivityLogHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RekapProdukController extends Controller
{
    /**
     * Tampilkan halaman Rekapitulasi Produk (Produksi vs Penjualan).
     * Mendukung filter range tanggal (tanggal_mulai – tanggal_selesai)
     * dan response JSON untuk DataTables server-side.
     */
    public function index(Request $request)
    {
        try {
            // ─── Filter tanggal range ───
            $tanggalMulai   = $request->input('tanggal_mulai');
            $tanggalSelesai = $request->input('tanggal_selesai');

            try {
                $carbonMulai = $tanggalMulai ? Carbon::parse($tanggalMulai)->startOfDay() : null;
            } catch (\Throwable $e) {
                $carbonMulai = null;
            }
            try {
                $carbonSelesai = $tanggalSelesai ? Carbon::parse($tanggalSelesai)->endOfDay() : null;
            } catch (\Throwable $e) {
                $carbonSelesai = null;
            }

            if ($carbonMulai && $carbonSelesai && $carbonSelesai->lt($carbonMulai)) {
                $carbonSelesai = (clone $carbonMulai)->endOfDay();
            }

            // ─── Filter tambahan ───
            $kategoriId      = $request->input('kategori_id');
            $statusAktivitas = $request->input('status_aktivitas', 'semua');

            // ─── 1) Agregat total produksi per produk ───
            $qProduksi = DB::table('produksis');
            if ($carbonMulai) $qProduksi->whereDate('tanggal', '>=', $carbonMulai->toDateString());
            if ($carbonSelesai) $qProduksi->whereDate('tanggal', '<=', $carbonSelesai->toDateString());
            $produksiPerProduk = $qProduksi
                ->groupBy('produk_id')
                ->selectRaw('produk_id, SUM(jumlah_hasil) as total_produksi')
                ->pluck('total_produksi', 'produk_id');

            // ─── 2) Agregat total penjualan sah per produk ───
            $qTerjual = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
                ->where('transaksi.status_pembayaran', 'Lunas')
                ->where(function ($q) {
                    $q->whereNull('transaksi.status_pesanan')
                      ->orWhere('transaksi.status_pesanan', '!=', Transaksi::STATUS_DIBATALKAN);
                })
                ->whereNotNull('detail_transaksi.produk_id');
            if ($carbonMulai) $qTerjual->whereDate('transaksi.created_at', '>=', $carbonMulai->toDateString());
            if ($carbonSelesai) $qTerjual->whereDate('transaksi.created_at', '<=', $carbonSelesai->toDateString());
            $terjualPerProduk = $qTerjual
                ->groupBy('detail_transaksi.produk_id')
                ->selectRaw('detail_transaksi.produk_id, SUM(detail_transaksi.jumlah) as total_terjual')
                ->pluck('total_terjual', 'produk_id');

            // ─── 3) Ambil produk dengan relasi kategori & detailProduks ───
            $queryProduk = Produk::with(['kategori', 'detailProduks']);

            if (!empty($kategoriId)) {
                $queryProduk->where('kategori_id', $kategoriId);
            }

            $semuaProduk = $queryProduk->orderBy('nama_produk')->get();

            // Petakan data produksi & penjualan ke setiap produk
            $rekapList = $semuaProduk->map(function ($produk) use ($produksiPerProduk, $terjualPerProduk) {
                $prod = (int) ($produksiPerProduk[$produk->id] ?? 0);
                $jual = (int) ($terjualPerProduk[$produk->id] ?? 0);
                $produk->total_produksi = $prod;
                $produk->total_terjual = $jual;
                $produk->selisih = $prod - $jual;
                $produk->nama_kategori = $produk->kategori->nama_kategori ?? '-';
                $produk->jumlah_varian = $produk->detailProduks ? $produk->detailProduks->count() : 0;
                $produk->stok = (int) $produk->stok;

                if ($prod > 0 && $jual > 0) {
                    $produk->status_label = 'Produksi & Terjual';
                } elseif ($prod > 0) {
                    $produk->status_label = 'Hanya Produksi';
                } elseif ($jual > 0) {
                    $produk->status_label = 'Hanya Penjualan';
                } else {
                    $produk->status_label = 'Tidak Ada';
                }

                return $produk;
            });

            // ─── Terapkan filter status aktivitas ───
            if ($statusAktivitas === 'ada_aktivitas') {
                $rekapList = $rekapList->filter(fn ($p) => $p->total_produksi > 0 || $p->total_terjual > 0);
            } elseif ($statusAktivitas === 'ada_produksi') {
                $rekapList = $rekapList->filter(fn ($p) => $p->total_produksi > 0);
            } elseif ($statusAktivitas === 'ada_penjualan') {
                $rekapList = $rekapList->filter(fn ($p) => $p->total_terjual > 0);
            } elseif ($statusAktivitas === 'tidak_ada_aktivitas') {
                $rekapList = $rekapList->filter(fn ($p) => $p->total_produksi === 0 && $p->total_terjual === 0);
            }

            // ─── Terapkan global search jika dikirim via DataTables ───
            $search = $request->input('search.value');
            if (!empty($search)) {
                $kw = strtolower(trim($search));
                $rekapList = $rekapList->filter(function ($p) use ($kw) {
                    return str_contains(strtolower($p->nama_produk ?? ''), $kw)
                        || str_contains(strtolower($p->kode_produk ?? ''), $kw)
                        || str_contains(strtolower($p->nama_kategori ?? ''), $kw)
                        || str_contains(strtolower($p->status_label ?? ''), $kw);
                });
            }

            // ─── Response AJAX untuk DataTables ───
            if ($request->ajax()) {
                return DataTables::of($rekapList->values())
                    ->addColumn('produk_html', function ($item) {
                        $fotoUrl = $item->foto
                            ? asset('storage/' . $item->foto)
                            : optional($item->detailProduks->first(fn ($d) => $d->foto))->foto_url;

                        $imgHtml = $fotoUrl
                            ? '<img src="' . e($fotoUrl) . '" alt="' . e($item->nama_produk) . '" class="w-10 h-10 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0 shadow-xs">'
                            : '<div class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-xs font-bold shrink-0 bg-gray-50 dark:bg-gray-800"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>';

                        $subText = '';
                        if ($item->kode_produk) {
                            $subText .= '<span class="font-mono text-xxs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">' . e($item->kode_produk) . '</span>';
                        }
                        if ($item->jumlah_varian > 1) {
                            $subText .= ' <span class="text-xxs text-amber-600 dark:text-amber-400 font-medium">(' . $item->jumlah_varian . ' varian)</span>';
                        }

                        return '<div class="flex items-center gap-3">' . $imgHtml . '<div class="min-w-0"><p class="font-bold text-gray-900 dark:text-white truncate">' . e($item->nama_produk) . '</p><div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500 dark:text-gray-400">' . $subText . '</div></div></div>';
                    })
                    ->addColumn('kategori_html', function ($item) {
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">' . e($item->nama_kategori) . '</span>';
                    })
                    ->addColumn('produksi_html', function ($item) {
                        if ($item->total_produksi > 0) {
                            return '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 shadow-xs">' . number_format($item->total_produksi, 0, ',', '.') . ' produksi</span>';
                        }
                        return '<span class="text-gray-400 dark:text-gray-600 font-medium text-xs">0</span>';
                    })
                    ->addColumn('terjual_html', function ($item) {
                        if ($item->total_terjual > 0) {
                            return '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 shadow-xs">' . number_format($item->total_terjual, 0, ',', '.') . ' terjual</span>';
                        }
                        return '<span class="text-gray-400 dark:text-gray-600 font-medium text-xs">0</span>';
                    })
                    ->addColumn('selisih_html', function ($item) {
                        if ($item->total_produksi === 0 && $item->total_terjual === 0) {
                            return '<span class="text-gray-400 dark:text-gray-600 font-medium text-xs">-</span>';
                        }
                        if ($item->selisih > 0) {
                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400">+' . number_format($item->selisih, 0, ',', '.') . ' sisa</span>';
                        }
                        if ($item->selisih < 0) {
                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">' . number_format($item->selisih, 0, ',', '.') . '</span>';
                        }
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">0 (Imbang)</span>';
                    })
                    ->addColumn('stok_html', function ($item) {
                        $stok = (int) $item->stok;
                        $colorClass = $stok <= 0
                            ? 'text-red-600 dark:text-red-400 font-extrabold'
                            : ($stok <= ($item->stok_minimum ?? 5) ? 'text-orange-600 dark:text-orange-400' : 'text-gray-800 dark:text-gray-200');
                        return '<span class="' . $colorClass . '">' . number_format($stok, 0, ',', '.') . ' pcs</span>';
                    })
                    ->addColumn('status_html', function ($item) {
                        if ($item->total_produksi > 0 && $item->total_terjual > 0) {
                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xxs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">Produksi & Terjual</span>';
                        }
                        if ($item->total_produksi > 0) {
                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xxs font-semibold bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400">Hanya Produksi</span>';
                        }
                        if ($item->total_terjual > 0) {
                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xxs font-semibold bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-400">Hanya Penjualan</span>';
                        }
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xxs font-medium bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500">Tidak Ada</span>';
                    })
                    ->addColumn('aksi', function ($item) {
                        if ($item->jumlah_varian > 1) {
                            return '<div class="inline-flex items-center gap-0.5 justify-center"><button type="button" @click="lihatVarian(' . $item->id . ')" title="Rincian Varian" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button></div>';
                        }
                        return '<span class="text-xs text-gray-400">-</span>';
                    })
                    ->rawColumns(['produk_html', 'kategori_html', 'produksi_html', 'terjual_html', 'selisih_html', 'stok_html', 'status_html', 'aksi'])
                    ->make(true);
            }

            // Daftar kategori untuk dropdown filter
            $kategoris = KategoriProduk::orderBy('nama_kategori')->get();

            return view('pages.laporan.rekap-produk', [
                'title'            => 'Rekap Produk',
                'tanggalMulai'     => $tanggalMulai,
                'tanggalSelesai'   => $tanggalSelesai,
                'kategoris'        => $kategoris,
                'kategoriId'       => $kategoriId,
                'statusAktivitas'  => $statusAktivitas,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Detail rincian varian untuk modal.
     */
    public function detailVarian(Request $request, $id)
    {
        try {
            $produk = Produk::with(['detailProduks', 'kategori'])->findOrFail($id);

            $tanggalMulai   = $request->input('tanggal_mulai');
            $tanggalSelesai = $request->input('tanggal_selesai');

            try {
                $carbonMulai = $tanggalMulai ? Carbon::parse($tanggalMulai)->startOfDay() : null;
            } catch (\Throwable $e) {
                $carbonMulai = null;
            }
            try {
                $carbonSelesai = $tanggalSelesai ? Carbon::parse($tanggalSelesai)->endOfDay() : null;
            } catch (\Throwable $e) {
                $carbonSelesai = null;
            }

            if ($carbonMulai && $carbonSelesai && $carbonSelesai->lt($carbonMulai)) {
                $carbonSelesai = (clone $carbonMulai)->endOfDay();
            }

            // Agregat produksi varian
            $qProduksi = DB::table('produksi_varians')
                ->join('produksis', 'produksi_varians.produksi_id', '=', 'produksis.id')
                ->where('produksis.produk_id', $produk->id);
            if ($carbonMulai) $qProduksi->whereDate('produksis.tanggal', '>=', $carbonMulai->toDateString());
            if ($carbonSelesai) $qProduksi->whereDate('produksis.tanggal', '<=', $carbonSelesai->toDateString());
            $prodPerDetail = $qProduksi
                ->groupBy('produksi_varians.detail_produk_id')
                ->selectRaw('produksi_varians.detail_produk_id, SUM(produksi_varians.jumlah_hasil) as total')
                ->pluck('total', 'detail_produk_id');

            // Agregat penjualan varian
            $qTerjual = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
                ->where('detail_transaksi.produk_id', $produk->id)
                ->where('transaksi.status_pembayaran', 'Lunas')
                ->where(function ($q) {
                    $q->whereNull('transaksi.status_pesanan')
                      ->orWhere('transaksi.status_pesanan', '!=', Transaksi::STATUS_DIBATALKAN);
                });
            if ($carbonMulai) $qTerjual->whereDate('transaksi.created_at', '>=', $carbonMulai->toDateString());
            if ($carbonSelesai) $qTerjual->whereDate('transaksi.created_at', '<=', $carbonSelesai->toDateString());
            $jualPerDetail = $qTerjual
                ->groupBy('detail_transaksi.detail_produk_id')
                ->selectRaw('detail_transaksi.detail_produk_id, SUM(detail_transaksi.jumlah) as total')
                ->pluck('total', 'detail_produk_id');

            $varians = $produk->detailProduks->map(function ($dp) use ($prodPerDetail, $jualPerDetail) {
                $p = (int) ($prodPerDetail[$dp->id] ?? 0);
                $j = (int) ($jualPerDetail[$dp->id] ?? 0);
                $label = trim(($dp->ukuran ?? '') . ' ' . ($dp->variasi ?? ''));
                if (!$label) $label = 'Standar';

                return [
                    'id'             => $dp->id,
                    'nama_varian'    => $label,
                    'kode_produk'    => $dp->kode_produk ?? '-',
                    'total_produksi' => $p,
                    'total_terjual'  => $j,
                    'selisih'        => $p - $j,
                    'stok'           => (int) $dp->stok,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'nama_produk' => $produk->nama_produk,
                    'kategori'    => $produk->kategori->nama_kategori ?? '-',
                    'varians'     => $varians,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
