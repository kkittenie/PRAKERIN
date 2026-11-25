<?php

namespace App\Http\Controllers;

use App\Models\Produksi;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\ProduksiBahanBaku;
use App\Models\DetailProduk;
use App\Models\ProduksiVarian;
use App\Events\StatusProduksiUpdate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Support\ActivityLogHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProduksiController extends Controller
{
    private function resolvePeriode(Request $request): array
    {
        $periode = $request->periode ?? 'semua';
        $tanggalRefInput = $request->tanggal_ref;

        switch ($periode) {
            case 'semua':
                $periode = 'semua';
                $tanggalRef = Carbon::now();
                break;

            case 'mingguan':
                try {
                    if ($tanggalRefInput && preg_match('/^(\d{4})-W(\d{2})$/', $tanggalRefInput, $m)) {
                        $tanggalRef = Carbon::now()->setISODate((int) $m[1], (int) $m[2]);
                    } else {
                        $tanggalRef = Carbon::now();
                    }
                } catch (Exception $e) {
                    $tanggalRef = Carbon::now();
                }
                break;

            case 'bulanan':
                try {
                    $tanggalRef = $tanggalRefInput
                        ? Carbon::parse($tanggalRefInput . '-01')
                        : Carbon::now();
                } catch (Exception $e) {
                    $tanggalRef = Carbon::now();
                }
                break;

            default:
                try {
                    $tanggalRef = $tanggalRefInput
                        ? Carbon::parse($tanggalRefInput)
                        : Carbon::today();
                } catch (Exception $e) {
                    $tanggalRef = Carbon::today();
                }
                $periode = 'harian';
                break;
        }

        return [$periode, $tanggalRef];
    }

    private function filteredProduksiQuery(Request $request, string $periode, Carbon $tanggalRef)
    {
        $search = is_array($request->search) ? ($request->search['value'] ?? null) : $request->search;
        $status = $request->status ?? 'semua';
        $now = Carbon::now();

        $kadaluarsaExpr = "
            CASE
                WHEN kadaluarsa_tipe = 'tanggal' THEN kadaluarsa_tanggal < ?
                WHEN kadaluarsa_tipe = 'jam' THEN DATE_ADD(CONCAT(tanggal, ' ', jam_produksi), INTERVAL kadaluarsa_jam HOUR) < ?
                ELSE 0
            END
        ";

        return Produksi::with(['produk.detailProduks', 'user', 'detailBahan.bahanBaku.satuan', 'produksiVarians'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('produk', function ($q) use ($search) {
                    $q->where('nama_produk', 'like', "%{$search}%")
                      ->orWhereHas('detailProduks', function ($qd) use ($search) {
                          $qd->where('kode_produk', 'like', "%{$search}%");
                      });
                });
            })
            ->when($status === 'kadaluarsa', function ($query) use ($kadaluarsaExpr, $now) {
                $query->whereRaw($kadaluarsaExpr, [$now->toDateString(), $now->toDateTimeString()]);
            })
            ->when($status === 'aktif', function ($query) use ($kadaluarsaExpr, $now) {
                $query->whereRaw('NOT (' . $kadaluarsaExpr . ')', [$now->toDateString(), $now->toDateTimeString()]);
            })
            // periode 'semua' sengaja tidak menambahkan filter tanggal apa pun.
            ->when($periode === 'harian', function ($query) use ($tanggalRef) {
                $query->whereDate('tanggal', $tanggalRef->toDateString());
            })
            ->when($periode === 'mingguan', function ($query) use ($tanggalRef) {
                $query->whereBetween('tanggal', [
                    $tanggalRef->copy()->startOfWeek()->toDateString(),
                    $tanggalRef->copy()->endOfWeek()->toDateString(),
                ]);
            })
            ->when($periode === 'bulanan', function ($query) use ($tanggalRef) {
                $query->whereYear('tanggal', $tanggalRef->year)
                    ->whereMonth('tanggal', $tanggalRef->month);
            });
    }

    public function liveStatus()
    {
        try {
            $produksiKadaluarsa = Produksi::with('produk')
                ->whereColumn('jumlah_disesuaikan', '<', 'jumlah_hasil')
                ->get()
                ->filter(fn ($item) => $item->is_kadaluarsa);

            return response()->json([
                'status' => 'success',
                'count_kadaluarsa' => $produksiKadaluarsa->count(),
                'items' => $produksiKadaluarsa->map(fn ($item) => [
                    'id' => $item->id,
                    'produk' => optional($item->produk)->nama_produk,
                    'sisa' => $item->sisa_belum_disesuaikan,
                ])->values(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $search = $request->search;
            $status = $request->status ?? 'semua';

            [$periode, $tanggalRef] = $this->resolvePeriode($request);

            if ($request->ajax()) {
                $query = $this->filteredProduksiQuery($request, $periode, $tanggalRef);

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->filterColumn('produk.nama_produk', function ($q, $keyword) {
                        $q->whereHas('produk', function ($qp) use ($keyword) {
                            $qp->where('nama_produk', 'like', "%{$keyword}%")
                              ->orWhereHas('detailProduks', function ($qd) use ($keyword) {
                                  $qd->where('kode_produk', 'like', "%{$keyword}%");
                              });
                        });
                    })
                    ->filterColumn('user.name', function ($q, $keyword) {
                        $q->whereHas('user', function ($qu) use ($keyword) {
                            $qu->where('name', 'like', "%{$keyword}%");
                        });
                    })
                    // Urutan waktu: tanggal, lalu jam produksi, lalu id, supaya batch yang dibuat
                    // belakangan selalu di atas walau tanggalnya sama dengan batch lain.
                    ->orderColumn('tanggal', function ($q, $order) {
                        $q->orderBy('produksis.tanggal', $order)
                          ->orderBy('produksis.jam_produksi', $order)
                          ->orderBy('produksis.id', $order);
                    })
                    ->orderColumn('produk.nama_produk', function ($q, $order) {
                        $q->join('produks', 'produksis.produk_id', '=', 'produks.id')
                          ->orderBy('produks.nama_produk', $order)
                          ->select('produksis.*');
                    })
                    ->orderColumn('user.name', function ($q, $order) {
                        $q->join('users', 'produksis.user_id', '=', 'users.id')
                          ->orderBy('users.name', $order)
                          ->select('produksis.*');
                    })
                    ->editColumn('tanggal', fn($item) => view('admin.produksi.partials.tanggal', compact('item'))->render())
                    ->addColumn('produk_html', fn($item) => view('admin.produksi.partials.produk', compact('item'))->render())
                    ->addColumn('hasil_html', fn($item) => view('admin.produksi.partials.hasil', compact('item'))->render())
                    ->editColumn('keterangan', fn($item) => view('admin.produksi.partials.keterangan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('admin.produksi.partials.aksi', compact('item'))->render())
                    ->setRowAttr([
                        'data-produksi-row' => 'true',
                        'data-kadaluarsa-waktu' => fn($item) => $item->kadaluarsa_waktu ? $item->kadaluarsa_waktu->toIso8601String() : '',
                        'data-sisa' => fn($item) => $item->sisa_belum_disesuaikan,
                    ])
                    ->setRowClass(fn($item) => $item->perlu_tindakan ? 'bg-red-50/70 dark:bg-red-500/10' : '')
                    ->rawColumns(['tanggal', 'produk_html', 'hasil_html', 'keterangan', 'aksi'])
                    ->make(true);
            }

            $produksiKadaluarsa = Produksi::with('produk')
                ->get()
                ->filter(function ($item) {
                    return $item->is_kadaluarsa
                        && $item->sisa_belum_disesuaikan > 0;
                });

            return view(
                'admin.produksi.index',
                compact('search', 'status', 'periode', 'tanggalRef', 'produksiKadaluarsa')
            );
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data produksi.'], 500);
            }
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            // Hanya kirim data yang dipakai form: produk + detail produk / varian AKTIF.
            $produk = Produk::with(['detailProduks', 'varianProduks'])
                ->orderBy('nama_produk')
                ->get()
                ->map(fn (Produk $p) => [
                    'id' => $p->id,
                    'kode_detail' => $p->detailProduks->pluck('kode_barcode')->filter()->take(3)->implode(', ')
                        . ($p->detailProduks->pluck('kode_barcode')->filter()->count() > 3
                            ? ' +' . ($p->detailProduks->pluck('kode_barcode')->filter()->count() - 3) . ' lainnya'
                            : ''),
                    'kode_cari' => $p->detailProduks->pluck('kode_barcode')->filter()->implode(' '),
                    'nama_produk' => $p->nama_produk,
                    'foto' => $p->foto,
                    'is_global' => $p->is_global,
                    'punya_varian' => $p->punya_varian,
                    'punya_varian_baru' => $p->punya_varian,
                    'daftar_varian_produksi' => $p->daftarVarianPilihan()->values()->all(),
                ])
                ->values();

            $bahanBaku = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();

            return view('admin.produksi.create', compact('produk', 'bahanBaku'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            if ($request->has('produk_items') && is_array($request->produk_items)) {
                return $this->simpanProduksiBanyak($request);
            }

            $produk = Produk::with(['detailProduks', 'varianProduks'])->find($request->produk_id);

            if (!$produk) {
                return back()->withInput()->with('error', 'Pilih produk yang akan diproduksi.');
            }

            if ($produk->punya_varian) {
                return $this->simpanProduksiVarian($request, $produk);
            }

            return $this->simpanProduksiTanpaVarian($request, $produk);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Simpan produksi untuk banyak produk sekaligus dalam satu sesi produksi.
     * Mendukung produk dengan varian (Stok Bersama / Stok Per Varian) maupun produk tanpa varian.
     */
    private function simpanProduksiBanyak(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_produksi' => 'required|date_format:H:i',
            'keterangan' => 'nullable|string',

            'kadaluarsa_tipe' => 'required|in:tanggal,jam',
            'kadaluarsa_tanggal' => 'required_if:kadaluarsa_tipe,tanggal|nullable|date|after_or_equal:tanggal',
            'kadaluarsa_jam' => 'required_if:kadaluarsa_tipe,jam|nullable|integer|min:1',

            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => ['required', 'distinct', 'exists:bahan_bakus,id'],

            'jumlah_terpakai' => 'required|array|min:1',
            'jumlah_terpakai.*' => [
                'required',
                'numeric',
                'gt:0',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/\d+/', $attribute, $match);
                    $index = $match[0] ?? null;
                    $bahanId = $request->bahan_baku_id[$index] ?? null;

                    if (!$bahanId) {
                        return;
                    }

                    $bahan = BahanBaku::with('satuan')->find($bahanId);

                    if ($bahan && $bahan->satuan?->tipe === 'bulat' && floor($value) != $value) {
                        $fail("Jumlah dipakai untuk {$bahan->nama_bahan} harus bilangan bulat (satuan {$bahan->satuan->nama}, tidak boleh desimal).");
                    }
                },
            ],

            'produk_items' => 'required|array|min:1',
            'produk_items.*.produk_id' => 'required|exists:produks,id',
        ]);

        $adaBahan = false;
        foreach ($request->jumlah_terpakai as $jumlah) {
            if ($jumlah > 0) {
                $adaBahan = true;
                break;
            }
        }

        if (!$adaBahan) {
            return back()->withInput()->with('error', 'Minimal pilih satu bahan baku yang digunakan.');
        }

        $items = (array) $request->input('produk_items', []);
        if (empty($items)) {
            return back()->withInput()->with('error', 'Tambahkan minimal satu produk yang diproduksi.');
        }

        $produkIds = [];
        $parsedProducts = [];

        foreach ($items as $idx => $item) {
            $produkId = (int) ($item['produk_id'] ?? 0);
            if (!$produkId) {
                return back()->withInput()->with('error', 'Ada baris produk yang belum dipilih.');
            }

            if (in_array($produkId, $produkIds, true)) {
                $duplikat = Produk::find($produkId);
                return back()->withInput()->with('error', "Produk '{$duplikat?->nama_produk}' dipilih lebih dari satu kali. Gabungkan jumlahnya dalam satu baris produk.");
            }
            $produkIds[] = $produkId;

            $produk = Produk::with(['detailProduks', 'varianProduks'])->find($produkId);
            if (!$produk) {
                return back()->withInput()->with('error', 'Produk tidak ditemukan.');
            }

            if ($produk->punya_varian) {
                $hasilInput = (array) ($item['varian_hasil'] ?? []);
                $kebutuhanInput = (array) ($item['varian_kebutuhan'] ?? []);
                $rincian = [];
                $totalHasil = 0;
                $totalKebutuhan = 0;

                if ($produk->is_global) {
                    $detailUtama = $produk->daftarDetail()->first();
                    if (!$detailUtama) {
                        return back()->withInput()->with('error', "Produk Stok Bersama {$produk->nama_produk} belum memiliki detail produk/stok.");
                    }
                    $varianMap = $produk->varianProduks()->get()->keyBy('id');

                    foreach ($hasilInput as $key => $hasil) {
                        $hasil = (int) $hasil;
                        $kebutuhan = (int) ($kebutuhanInput[$key] ?? 0);

                        if ($hasil <= 0 && $kebutuhan <= 0) {
                            continue;
                        }

                        $vpId = str_starts_with((string)$key, 'vp_') ? (int) substr($key, 3) : (int) $key;
                        $varian = $varianMap->get($vpId);

                        if (!$varian) {
                            return back()->withInput()->with('error', "Ada varian tidak valid untuk {$produk->nama_produk}.");
                        }

                        if (!$varian->status_aktif) {
                            return back()->withInput()->with('error', "Varian {$varian->nama_lengkap} ({$produk->nama_produk}) sedang dinonaktifkan.");
                        }

                        if ($kebutuhan > $hasil) {
                            return back()->withInput()->with('error', "Pesanan varian {$varian->nama_lengkap} ({$produk->nama_produk}) tidak boleh melebihi Jumlah Hasil varian.");
                        }

                        $rincian[] = [
                            'is_stok_bersama' => true,
                            'detail_produk_id' => $detailUtama->id,
                            'varian_produk_id' => $varian->id,
                            'ukuran' => $varian->ukuran,
                            'variasi' => $varian->variasi,
                            'kode_barcode' => $varian->kode_produk,
                            'harga_jual' => $detailUtama->harga_jual,
                            'nama_lengkap' => $varian->nama_lengkap,
                            'hasil' => $hasil,
                            'kebutuhan' => $kebutuhan,
                        ];
                        $totalHasil += $hasil;
                        $totalKebutuhan += $kebutuhan;
                    }
                } else {
                    // Stok Per Varian
                    $detailMap = DetailProduk::where('produk_id', $produk->id)->get()->keyBy('id');

                    foreach ($hasilInput as $key => $hasil) {
                        $detailId = (int) $key;
                        $hasil = (int) $hasil;
                        $kebutuhan = (int) ($kebutuhanInput[$key] ?? 0);

                        if ($hasil <= 0 && $kebutuhan <= 0) {
                            continue;
                        }

                        $varian = $detailMap->get($detailId);

                        if (!$varian) {
                            return back()->withInput()->with('error', "Ada varian tidak valid untuk {$produk->nama_produk}.");
                        }

                        if (!$varian->status_aktif) {
                            return back()->withInput()->with('error', "Varian {$varian->nama_lengkap} ({$produk->nama_produk}) sedang dinonaktifkan.");
                        }

                        if ($kebutuhan > $hasil) {
                            return back()->withInput()->with('error', "Pesanan varian {$varian->nama_lengkap} ({$produk->nama_produk}) tidak boleh melebihi Jumlah Hasil varian.");
                        }

                        $rincian[] = [
                            'is_stok_bersama' => false,
                            'detail_produk_id' => $varian->id,
                            'varian_produk_id' => null,
                            'ukuran' => $varian->ukuran,
                            'variasi' => $varian->variasi,
                            'kode_barcode' => $varian->kode_barcode,
                            'harga_jual' => $varian->harga_jual,
                            'nama_lengkap' => $varian->nama_lengkap,
                            'hasil' => $hasil,
                            'kebutuhan' => $kebutuhan,
                        ];
                        $totalHasil += $hasil;
                        $totalKebutuhan += $kebutuhan;
                    }
                }

                if ($totalHasil <= 0) {
                    return back()->withInput()->with('error', "Isi Jumlah Hasil minimal pada satu varian untuk produk {$produk->nama_produk}.");
                }

                $parsedProducts[] = [
                    'produk' => $produk,
                    'punya_varian' => true,
                    'jumlah_hasil' => $totalHasil,
                    'total_kebutuhan' => $totalKebutuhan,
                    'rincian' => $rincian,
                ];
            } else {
                // Produk Tanpa Varian
                $hasil = (int) ($item['jumlah_hasil'] ?? 0);
                $kebutuhan = (int) ($item['kebutuhan_produksi'] ?? 0);

                if ($hasil <= 0) {
                    return back()->withInput()->with('error', "Jumlah Hasil Produksi untuk produk {$produk->nama_produk} minimal 1.");
                }

                if ($kebutuhan > $hasil) {
                    return back()->withInput()->with('error', "Pesanan untuk produk {$produk->nama_produk} tidak boleh melebihi Jumlah Hasil Produksi.");
                }

                $detailUtama = $produk->daftarDetail()->first();
                if (!$detailUtama) {
                    return back()->withInput()->with('error', "Produk {$produk->nama_produk} belum memiliki detail produk/stok.");
                }

                $parsedProducts[] = [
                    'produk' => $produk,
                    'punya_varian' => false,
                    'jumlah_hasil' => $hasil,
                    'total_kebutuhan' => $kebutuhan,
                    'detail_utama' => $detailUtama,
                ];
            }
        }

        $totalSemuaHasil = array_sum(array_column($parsedProducts, 'jumlah_hasil'));
        $totalCount = count($parsedProducts);

        DB::transaction(function () use ($request, $parsedProducts, $totalSemuaHasil, $totalCount) {
            // 1. Validasi & potong stok Bahan Baku
            foreach ($request->bahan_baku_id as $i => $id) {
                $pakai = (float) $request->jumlah_terpakai[$i];
                if ($pakai <= 0) continue;

                $bahan = BahanBaku::lockForUpdate()->findOrFail($id);
                if ($bahan->stok < $pakai) {
                    throw new Exception("Stok {$bahan->nama_bahan} tidak mencukupi.");
                }
                $bahan->decrement('stok', $pakai);
            }

            $allocatedBahan = [];

            // 2. Simpan setiap produk
            foreach ($parsedProducts as $pIndex => $pData) {
                $produk = $pData['produk'];
                $hasil = $pData['jumlah_hasil'];
                $kebutuhan = $pData['total_kebutuhan'];
                $isLastProduct = ($pIndex === $totalCount - 1);

                $produksi = Produksi::create([
                    'tanggal' => $request->tanggal,
                    'jam_produksi' => $request->jam_produksi,
                    'produk_id' => $produk->id,
                    'jumlah_hasil' => $hasil,
                    'jumlah_besar' => 0,
                    'jumlah_kecil' => 0,
                    'kebutuhan_produksi' => $kebutuhan,
                    'kebutuhan_besar' => 0,
                    'kebutuhan_kecil' => 0,
                    'user_id' => Auth::id(),
                    'keterangan' => $request->keterangan,
                    'kadaluarsa_tipe' => $request->kadaluarsa_tipe,
                    'kadaluarsa_tanggal' => $request->kadaluarsa_tipe === 'tanggal' ? $request->kadaluarsa_tanggal : null,
                    'kadaluarsa_jam' => $request->kadaluarsa_tipe === 'jam' ? $request->kadaluarsa_jam : null,
                    'status' => 'belum kadaluwarsa',
                ]);

                // Distribusi bahan baku
                foreach ($request->bahan_baku_id as $i => $id) {
                    $totalPakai = (float) $request->jumlah_terpakai[$i];
                    if ($totalPakai <= 0) continue;

                    if ($isLastProduct) {
                        $terpakaiItem = max(0, round($totalPakai - ($allocatedBahan[$id] ?? 0), 4));
                    } else {
                        $proporsi = $totalSemuaHasil > 0 ? ($hasil / $totalSemuaHasil) : (1 / $totalCount);
                        $terpakaiItem = round($totalPakai * $proporsi, 4);
                        $allocatedBahan[$id] = ($allocatedBahan[$id] ?? 0) + $terpakaiItem;
                    }

                    if ($terpakaiItem > 0) {
                        ProduksiBahanBaku::create([
                            'produksi_id' => $produksi->id,
                            'bahan_baku_id' => $id,
                            'jumlah_terpakai' => $terpakaiItem,
                        ]);
                    }
                }

                $produkTerkunci = Produk::lockForUpdate()->findOrFail($produk->id);

                if ($pData['punya_varian']) {
                    $rincian = $pData['rincian'];
                    if ($produk->is_global) {
                        $detailUtama = DetailProduk::lockForUpdate()->findOrFail($rincian[0]['detail_produk_id']);
                        $detailUtama->stok = (int) $detailUtama->stok + $hasil;
                        if ($kebutuhan > 0) {
                            if ($detailUtama->stok < $kebutuhan) {
                                throw new Exception("Stok untuk {$produkTerkunci->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                            }
                            $detailUtama->stok -= $kebutuhan;
                        }
                        $detailUtama->save();

                        foreach ($rincian as $r) {
                            ProduksiVarian::create([
                                'produksi_id' => $produksi->id,
                                'detail_produk_id' => $r['detail_produk_id'],
                                'varian_produk_id' => $r['varian_produk_id'],
                                'ukuran' => $r['ukuran'],
                                'variasi' => $r['variasi'],
                                'kode_barcode' => $r['kode_barcode'],
                                'harga_jual' => $r['harga_jual'],
                                'jumlah_hasil' => $r['hasil'],
                                'kebutuhan' => $r['kebutuhan'],
                            ]);
                        }
                    } else {
                        foreach ($rincian as $r) {
                            $varian = DetailProduk::lockForUpdate()->findOrFail($r['detail_produk_id']);
                            $varian->stok = (int) $varian->stok + $r['hasil'];

                            if ($r['kebutuhan'] > 0) {
                                if ($varian->stok < $r['kebutuhan']) {
                                    throw new Exception("Stok {$varian->nama_lengkap} untuk {$produkTerkunci->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                                }
                                $varian->stok -= $r['kebutuhan'];
                            }

                            $varian->save();

                            ProduksiVarian::create([
                                'produksi_id' => $produksi->id,
                                'detail_produk_id' => $varian->id,
                                'varian_produk_id' => null,
                                'ukuran' => $varian->ukuran,
                                'variasi' => $varian->variasi,
                                'kode_barcode' => $varian->kode_barcode,
                                'harga_jual' => $varian->harga_jual,
                                'jumlah_hasil' => $r['hasil'],
                                'kebutuhan' => $r['kebutuhan'],
                            ]);
                        }
                    }
                } else {
                    $detail = DetailProduk::lockForUpdate()->findOrFail($pData['detail_utama']->id);
                    $detail->stok = (int) $detail->stok + $hasil;
                    if ($kebutuhan > 0) {
                        if ($detail->stok < $kebutuhan) {
                            throw new Exception("Stok untuk {$produkTerkunci->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                        }
                        $detail->stok -= $kebutuhan;
                    }
                    $detail->save();

                    ProduksiVarian::create([
                        'produksi_id' => $produksi->id,
                        'detail_produk_id' => $detail->id,
                        'varian_produk_id' => null,
                        'ukuran' => $detail->ukuran,
                        'variasi' => $detail->variasi,
                        'kode_barcode' => $detail->kode_barcode,
                        'harga_jual' => $detail->harga_jual,
                        'jumlah_hasil' => $hasil,
                        'kebutuhan' => $kebutuhan,
                    ]);
                }

                $this->sinkronStokProdukDariVarian($produkTerkunci);

                event(new StatusProduksiUpdate($produksi));

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produksi)
                    ->withProperties([
                        'produk_id' => $produk->id,
                        'jumlah_hasil' => $hasil,
                        'kebutuhan_produksi' => $kebutuhan,
                    ])
                    ->log('menambahkan produksi');
            }
        });

        $pesanSukses = $totalCount > 1
            ? "Produksi berhasil disimpan untuk {$totalCount} produk ({$totalSemuaHasil} pcs)."
            : "Produksi berhasil disimpan ({$totalSemuaHasil} pcs).";

        return redirect()->route('produksi.index')->with('success', $pesanSukses);
    }

    public function show(Produksi $produksi)
    {
        try {
            $produksi->load(['produk', 'user', 'detailBahan.bahanBaku.satuan', 'produksiVarians']);

            return view('admin.produksi.show', compact('produksi'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Label barcode: satu kelompok label per detail produk, jumlahnya sebanyak hasil produksi.
     * Kode memakai kode detail saat ini; kalau detailnya sudah dihapus, pakai salinan saat produksi.
     */
    public function label(Request $request, Produksi $produksi)
    {
        try {
            $produksi->load(['produk', 'produksiVarians.detailProduk']);

            $labelItems = $produksi->produksiVarians
                ->filter(fn ($item) => $item->jumlah_hasil > 0)
                ->map(fn ($item) => [
                    'kode_barcode' => optional($item->detailProduk)->kode_barcode ?: ($item->kode_barcode ?: '-'),
                    'nama_ukuran' => $item->ukuran,
                    'nama_rasa' => $item->variasi,
                    'harga_jual' => optional($item->detailProduk)->harga_jual ?? $item->harga_jual,
                    'jumlah' => (int) $item->jumlah_hasil,
                ])
                ->values();

            $totalLabel = (int) $labelItems->sum('jumlah');

            return view('admin.produksi.label', compact('produksi', 'labelItems', 'totalLabel'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function pdf(Request $request)
    {
        try {
            [$periode, $tanggalRef] = $this->resolvePeriode($request);

            $produksi = $this->filteredProduksiQuery($request, $periode, $tanggalRef)
                ->orderBy('tanggal')
                ->orderBy('jam_produksi')
                ->get();

            $pdf = Pdf::loadView(
                'admin.produksi.pdf',
                compact('produksi', 'periode', 'tanggalRef')
            )->setPaper('a4', 'portrait');

            return $pdf->stream('laporan-produksi-' . now()->format('Ymd_His') . '.pdf');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function excel(Request $request)
    {
        try {
            [$periode, $tanggalRef] = $this->resolvePeriode($request);

            $produksi = $this->filteredProduksiQuery($request, $periode, $tanggalRef)
                ->orderBy('tanggal')
                ->orderBy('jam_produksi')
                ->get();

            $columnHeaders = [
                'Tanggal Produksi',
                'Jam Produksi',
                'Kadaluarsa',
                'Produk',
                'Jumlah Hasil',
                'Rincian Ukuran / Rasa',
                'Dibuat Oleh',
                'Keterangan',
                'Nama Bahan',
                'Jumlah Dipakai',
            ];
            $lastCol = 'J'; // sesuaikan jika jumlah kolom berubah

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Produksi');

            $sheet->fromArray($columnHeaders, null, 'A1');
            $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);

            $row = 2;
            foreach ($produksi as $item) {
                // Satu produksi = satu baris. Kalau bahan bakunya lebih dari satu,
                // nama bahan dan jumlahnya digabung dalam satu sel (satu bahan per baris teks).
                if ($item->detailBahan->isEmpty()) {
                    $namaBahan = '-';
                    $jumlahDipakai = '-';
                } else {
                    $namaBahan = $item->detailBahan
                        ->map(fn ($d) => $d->bahanBaku->nama_bahan)
                        ->implode("\n");
                    $jumlahDipakai = $item->detailBahan
                        ->map(fn ($d) => $d->jumlah_terpakai . ' ' . ($d->bahanBaku->satuan->nama ?? ''))
                        ->implode("\n");
                }

                $sheet->fromArray([
                    $item->tanggal,
                    $item->jam_produksi,
                    $item->kadaluarsa_label,
                    $item->produk->nama_produk,
                    $item->jumlah_hasil,
                    $item->punya_rincian_rasa ? $item->rincian_rasa : $item->rincian_ukuran,
                    $item->user->name,
                    $item->keterangan,
                    $namaBahan,
                    $jumlahDipakai,
                ], null, "A{$row}");
                $row++;
            }

            $lastRow = $row - 1;

            // Lebarkan setiap kolom otomatis mengikuti isi terpanjang, biar tidak kepotong
            foreach (range('A', $lastCol) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Rata tengah (horizontal & vertical) untuk seluruh sel berisi data
            $sheet->getStyle("A1:{$lastCol}{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            // Kolom Produk (D) dan Nama Bahan (I) dibuat rata kiri, tapi judul header tetap di tengah
            $sheet->getStyle("D2:D{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("I2:I{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Nama bahan & jumlah dipakai bisa berisi beberapa baris teks, jadi wajib wrap text
            $sheet->getStyle("I2:J{$lastRow}")->getAlignment()->setWrapText(true);

            // Border tipis di semua sel tabel biar rapi
            $sheet->getStyle("A1:{$lastCol}{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            $filename = 'laporan-produksi-' . now()->format('YmdHis') . '.xlsx';

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Produksi $produksi)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        try {
            return back()->with('error', 'Data produksi tidak dapat dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Kurangi stok produk kadaluwarsa: detail yang dikurangi dipilih lewat varian_id
     * (detail nonaktif pun boleh, karena stok kadaluwarsa tetap harus dibuang).
     */
    public function kurangiStok(Request $request, Produksi $produksi)
    {
        try {
            $produk = $produksi->produk;

            if (!$produk) {
                return back()->with('error', 'Produk untuk produksi ini sudah tidak ada.');
            }

            return $this->kurangiStokVarian($request, $produksi, $produk);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Simpan produksi untuk produk yang punya varian dari form produk (tabel detail_produks).
     * Input: varian_hasil[detail_produk_id] dan varian_kebutuhan[detail_produk_id].
     * Stok varian bertambah sebesar hasil, lalu berkurang sebesar pesanan (kebutuhan).
     */
    private function simpanProduksiTanpaVarian(Request $request, Produk $produk)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_produksi' => 'required|date_format:H:i',
            'produk_id' => 'required|exists:produks,id',
            'jumlah_hasil' => 'required|integer|min:1',
            'kebutuhan_produksi' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',

            'kadaluarsa_tipe' => 'required|in:tanggal,jam',
            'kadaluarsa_tanggal' => 'required_if:kadaluarsa_tipe,tanggal|nullable|date|after_or_equal:tanggal',
            'kadaluarsa_jam' => 'required_if:kadaluarsa_tipe,jam|nullable|integer|min:1',

            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => ['required', 'distinct', 'exists:bahan_bakus,id'],

            'jumlah_terpakai' => 'required|array|min:1',
            'jumlah_terpakai.*' => [
                'required',
                'numeric',
                'gt:0',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/\d+/', $attribute, $match);
                    $index = $match[0] ?? null;
                    $bahanId = $request->bahan_baku_id[$index] ?? null;

                    if (!$bahanId) {
                        return;
                    }

                    $bahan = BahanBaku::with('satuan')->find($bahanId);

                    if ($bahan && $bahan->satuan?->tipe === 'bulat' && floor($value) != $value) {
                        $fail("Jumlah dipakai untuk {$bahan->nama_bahan} harus bilangan bulat (satuan {$bahan->satuan->nama}, tidak boleh desimal).");
                    }
                },
            ],
        ]);

        $kebutuhan = (int) $request->input('kebutuhan_produksi', 0);
        $hasil = (int) $request->jumlah_hasil;

        if ($kebutuhan > $hasil) {
            return back()->withInput()->with('error', 'Pesanan tidak boleh melebihi Jumlah Hasil Produksi.');
        }

        $adaBahan = false;
        foreach ($request->jumlah_terpakai as $jumlah) {
            if ($jumlah > 0) {
                $adaBahan = true;
                break;
            }
        }

        if (!$adaBahan) {
            return back()->withInput()->with('error', 'Minimal pilih satu bahan baku yang digunakan.');
        }

        $detailUtama = $produk->daftarDetail()->first();
        if (!$detailUtama) {
            return back()->withInput()->with('error', 'Produk ini belum memiliki detail produk/stok.');
        }

        $produksi = null;
        DB::transaction(function () use ($request, $produk, $detailUtama, $hasil, $kebutuhan, &$produksi) {
            $produksi = Produksi::create([
                'tanggal' => $request->tanggal,
                'jam_produksi' => $request->jam_produksi,
                'produk_id' => $produk->id,
                'jumlah_hasil' => $hasil,
                'jumlah_besar' => 0,
                'jumlah_kecil' => 0,
                'kebutuhan_produksi' => $kebutuhan,
                'kebutuhan_besar' => 0,
                'kebutuhan_kecil' => 0,
                'user_id' => Auth::id(),
                'keterangan' => $request->keterangan,
                'kadaluarsa_tipe' => $request->kadaluarsa_tipe,
                'kadaluarsa_tanggal' => $request->kadaluarsa_tipe === 'tanggal' ? $request->kadaluarsa_tanggal : null,
                'kadaluarsa_jam' => $request->kadaluarsa_tipe === 'jam' ? $request->kadaluarsa_jam : null,
                'status' => 'belum kadaluwarsa',
            ]);

            foreach ($request->bahan_baku_id as $i => $id) {
                $pakai = (float) $request->jumlah_terpakai[$i];
                if ($pakai <= 0) continue;

                $bahan = BahanBaku::lockForUpdate()->findOrFail($id);
                if ($bahan->stok < $pakai) {
                    throw new Exception("Stok {$bahan->nama_bahan} tidak mencukupi.");
                }

                ProduksiBahanBaku::create([
                    'produksi_id' => $produksi->id,
                    'bahan_baku_id' => $id,
                    'jumlah_terpakai' => $pakai,
                ]);

                $bahan->decrement('stok', $pakai);
            }

            $detail = DetailProduk::lockForUpdate()->findOrFail($detailUtama->id);
            $detail->stok = (int) $detail->stok + $hasil;
            if ($kebutuhan > 0) {
                if ($detail->stok < $kebutuhan) {
                    throw new Exception("Stok untuk {$produk->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                }
                $detail->stok -= $kebutuhan;
            }
            $detail->save();

            ProduksiVarian::create([
                'produksi_id' => $produksi->id,
                'detail_produk_id' => $detail->id,
                'varian_produk_id' => null,
                'ukuran' => $detail->ukuran,
                'variasi' => $detail->variasi,
                'kode_barcode' => $detail->kode_barcode,
                'harga_jual' => $detail->harga_jual,
                'jumlah_hasil' => $hasil,
                'kebutuhan' => $kebutuhan,
            ]);

            $this->sinkronStokProdukDariVarian(Produk::lockForUpdate()->findOrFail($produk->id));
        });

        event(new StatusProduksiUpdate($produksi));

        activity()
            ->causedBy(auth()->user())
            ->performedOn($produksi)
            ->withProperties([
                'produk_id' => $produk->id,
                'jumlah_hasil' => $hasil,
                'kebutuhan_produksi' => $kebutuhan,
                'bahan_baku_id' => $request->bahan_baku_id,
                'jumlah_terpakai' => $request->jumlah_terpakai,
            ])
            ->log('menambahkan produksi');

        return redirect()->route('produksi.index')->with(
            'success',
            "Produksi berhasil disimpan ({$produksi->jumlah_hasil} pcs)."
        );
    }

    /**
     * Simpan produksi untuk produk yang punya varian.
     * Mendukung Stok Per Varian (detail_produks) maupun Stok Bersama (varian_produks).
     */
    private function simpanProduksiVarian(Request $request, Produk $produk)
    {
        $hasilInput = (array) $request->input('varian_hasil', []);

        if (empty($hasilInput)) {
            return back()->withInput()->with('error', 'Produk ini belum punya varian aktif. Aktifkan atau tambahkan varian lewat halaman Edit Produk.');
        }

        $request->validate([
            'tanggal' => 'required|date',
            'jam_produksi' => 'required|date_format:H:i',
            'produk_id' => 'required|exists:produks,id',
            'jumlah_hasil' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',

            'varian_hasil' => 'required|array',
            'varian_hasil.*' => 'nullable|integer|min:0',
            'varian_kebutuhan' => 'nullable|array',
            'varian_kebutuhan.*' => 'nullable|integer|min:0',

            'kadaluarsa_tipe' => 'required|in:tanggal,jam',
            'kadaluarsa_tanggal' => 'required_if:kadaluarsa_tipe,tanggal|nullable|date|after_or_equal:tanggal',
            'kadaluarsa_jam' => 'required_if:kadaluarsa_tipe,jam|nullable|integer|min:1',

            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => ['required', 'distinct', 'exists:bahan_bakus,id'],

            'jumlah_terpakai' => 'required|array|min:1',
            'jumlah_terpakai.*' => [
                'required',
                'numeric',
                'gt:0',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/\d+/', $attribute, $match);
                    $index = $match[0] ?? null;
                    $bahanId = $request->bahan_baku_id[$index] ?? null;

                    if (!$bahanId) {
                        return;
                    }

                    $bahan = BahanBaku::with('satuan')->find($bahanId);

                    if ($bahan && $bahan->satuan?->tipe === 'bulat' && floor($value) != $value) {
                        $fail("Jumlah dipakai untuk {$bahan->nama_bahan} harus bilangan bulat (satuan {$bahan->satuan->nama}, tidak boleh desimal).");
                    }
                },
            ],
        ]);

        $kebutuhanInput = (array) $request->input('varian_kebutuhan', []);
        $rincian = [];
        $totalHasil = 0;
        $totalKebutuhan = 0;

        if ($produk->is_global) {
            $detailUtama = $produk->daftarDetail()->first();
            if (!$detailUtama) {
                return back()->withInput()->with('error', 'Produk Stok Bersama belum memiliki detail produk/stok.');
            }
            $varianProdukMap = $produk->varianProduks()->get()->keyBy('id');

            foreach ($hasilInput as $key => $hasil) {
                $hasil = (int) $hasil;
                $kebutuhan = (int) ($kebutuhanInput[$key] ?? 0);

                if ($hasil <= 0 && $kebutuhan <= 0) {
                    continue;
                }

                $vpId = str_starts_with((string)$key, 'vp_') ? (int) substr($key, 3) : (int) $key;
                $varian = $varianProdukMap->get($vpId);

                if (!$varian) {
                    return back()->withInput()->with('error', 'Ada varian yang tidak valid untuk produk ini.');
                }

                if (!$varian->status_aktif) {
                    return back()->withInput()->with('error', "Varian {$varian->nama_lengkap} sedang dinonaktifkan, tidak bisa diproduksi.");
                }

                if ($kebutuhan > $hasil) {
                    return back()->withInput()->with('error', "Pesanan varian {$varian->nama_lengkap} tidak boleh melebihi Jumlah Hasil varian itu sendiri.");
                }

                $rincian[] = [
                    'is_stok_bersama' => true,
                    'detail_produk_id' => $detailUtama->id,
                    'varian_produk_id' => $varian->id,
                    'ukuran' => $varian->ukuran,
                    'variasi' => $varian->variasi,
                    'kode_barcode' => $varian->kode_produk,
                    'harga_jual' => $detailUtama->harga_jual,
                    'nama_lengkap' => $varian->nama_lengkap,
                    'hasil' => $hasil,
                    'kebutuhan' => $kebutuhan,
                ];
                $totalHasil += $hasil;
                $totalKebutuhan += $kebutuhan;
            }
        } else {
            // Stok Per Varian
            $detailProdukMap = DetailProduk::where('produk_id', $produk->id)->get()->keyBy('id');

            foreach ($hasilInput as $key => $hasil) {
                $detailId = (int) $key;
                $hasil = (int) $hasil;
                $kebutuhan = (int) ($kebutuhanInput[$key] ?? 0);

                if ($hasil <= 0 && $kebutuhan <= 0) {
                    continue;
                }

                $varian = $detailProdukMap->get($detailId);

                if (!$varian) {
                    return back()->withInput()->with('error', 'Ada varian yang tidak valid untuk produk ini.');
                }

                if (!$varian->status_aktif) {
                    return back()->withInput()->with('error', "Varian {$varian->nama_lengkap} sedang dinonaktifkan, tidak bisa diproduksi.");
                }

                if ($kebutuhan > $hasil) {
                    return back()->withInput()->with('error', "Pesanan varian {$varian->nama_lengkap} tidak boleh melebihi Jumlah Hasil varian itu sendiri.");
                }

                $rincian[] = [
                    'is_stok_bersama' => false,
                    'detail_produk_id' => $varian->id,
                    'varian_produk_id' => null,
                    'ukuran' => $varian->ukuran,
                    'variasi' => $varian->variasi,
                    'kode_barcode' => $varian->kode_barcode,
                    'harga_jual' => $varian->harga_jual,
                    'nama_lengkap' => $varian->nama_lengkap,
                    'hasil' => $hasil,
                    'kebutuhan' => $kebutuhan,
                ];
                $totalHasil += $hasil;
                $totalKebutuhan += $kebutuhan;
            }
        }

        if ($totalHasil <= 0) {
            return back()->withInput()->with('error', 'Isi Jumlah Hasil minimal pada satu varian.');
        }

        if ($totalHasil !== (int) $request->jumlah_hasil) {
            return back()->withInput()->with('error', 'Total Jumlah Hasil semua varian harus sama dengan Jumlah Hasil Produksi.');
        }

        $adaBahan = false;
        foreach ($request->jumlah_terpakai as $jumlah) {
            if ($jumlah > 0) {
                $adaBahan = true;
                break;
            }
        }

        if (!$adaBahan) {
            return back()->withInput()->with('error', 'Minimal pilih satu bahan baku yang digunakan.');
        }

        $produksi = null;
        DB::transaction(function () use ($request, $produk, $rincian, $totalHasil, $totalKebutuhan, &$produksi) {
            $produksi = Produksi::create([
                'tanggal' => $request->tanggal,
                'jam_produksi' => $request->jam_produksi,
                'produk_id' => $produk->id,
                'jumlah_hasil' => $request->jumlah_hasil,
                'jumlah_besar' => 0,
                'jumlah_kecil' => 0,
                'kebutuhan_produksi' => $totalKebutuhan,
                'kebutuhan_besar' => 0,
                'kebutuhan_kecil' => 0,
                'user_id' => Auth::id(),
                'keterangan' => $request->keterangan,
                'kadaluarsa_tipe' => $request->kadaluarsa_tipe,
                'kadaluarsa_tanggal' => $request->kadaluarsa_tipe === 'tanggal' ? $request->kadaluarsa_tanggal : null,
                'kadaluarsa_jam' => $request->kadaluarsa_tipe === 'jam' ? $request->kadaluarsa_jam : null,
                'status' => 'belum kadaluwarsa',
            ]);

            foreach ($request->bahan_baku_id as $i => $id) {
                $pakai = (float) $request->jumlah_terpakai[$i];

                if ($pakai <= 0) {
                    continue;
                }

                $bahan = BahanBaku::lockForUpdate()->findOrFail($id);

                if ($bahan->stok < $pakai) {
                    throw new Exception("Stok {$bahan->nama_bahan} tidak mencukupi.");
                }

                ProduksiBahanBaku::create([
                    'produksi_id' => $produksi->id,
                    'bahan_baku_id' => $id,
                    'jumlah_terpakai' => $pakai,
                ]);

                $bahan->decrement('stok', $pakai);
            }

            $produkTerkunci = Produk::lockForUpdate()->findOrFail($produk->id);

            if ($produk->is_global) {
                $detailUtama = DetailProduk::lockForUpdate()->findOrFail($rincian[0]['detail_produk_id']);
                $detailUtama->stok = (int) $detailUtama->stok + $totalHasil;
                if ($totalKebutuhan > 0) {
                    if ($detailUtama->stok < $totalKebutuhan) {
                        throw new Exception("Stok untuk {$produkTerkunci->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                    }
                    $detailUtama->stok -= $totalKebutuhan;
                }
                $detailUtama->save();

                foreach ($rincian as $r) {
                    ProduksiVarian::create([
                        'produksi_id' => $produksi->id,
                        'detail_produk_id' => $r['detail_produk_id'],
                        'varian_produk_id' => $r['varian_produk_id'],
                        'ukuran' => $r['ukuran'],
                        'variasi' => $r['variasi'],
                        'kode_barcode' => $r['kode_barcode'],
                        'harga_jual' => $r['harga_jual'],
                        'jumlah_hasil' => $r['hasil'],
                        'kebutuhan' => $r['kebutuhan'],
                    ]);
                }
            } else {
                foreach ($rincian as $r) {
                    $varian = DetailProduk::lockForUpdate()->findOrFail($r['detail_produk_id']);
                    $varian->stok = (int) $varian->stok + $r['hasil'];

                    if ($r['kebutuhan'] > 0) {
                        if ($varian->stok < $r['kebutuhan']) {
                            throw new Exception("Stok {$varian->nama_lengkap} untuk {$produkTerkunci->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                        }
                        $varian->stok -= $r['kebutuhan'];
                    }

                    $varian->save();

                    ProduksiVarian::create([
                        'produksi_id' => $produksi->id,
                        'detail_produk_id' => $varian->id,
                        'varian_produk_id' => null,
                        'ukuran' => $varian->ukuran,
                        'variasi' => $varian->variasi,
                        'kode_barcode' => $varian->kode_barcode,
                        'harga_jual' => $varian->harga_jual,
                        'jumlah_hasil' => $r['hasil'],
                        'kebutuhan' => $r['kebutuhan'],
                    ]);
                }
            }

            $this->sinkronStokProdukDariVarian($produkTerkunci);
        });

        event(new StatusProduksiUpdate($produksi));

        activity()
            ->causedBy(auth()->user())
            ->performedOn($produksi)
            ->withProperties([
                'produk_id' => $produk->id,
                'jumlah_hasil' => $request->jumlah_hasil,
                'kebutuhan_produksi' => $totalKebutuhan,
                'rincian_varian' => $rincian,
                'bahan_baku_id' => $request->bahan_baku_id,
                'jumlah_terpakai' => $request->jumlah_terpakai,
            ])
            ->log('menambahkan produksi');

        $produksi->load('produksiVarians');
        $ringkas = $produksi->produksiVarians
            ->filter(fn ($v) => $v->jumlah_hasil > 0)
            ->map(fn ($v) => $v->nama_lengkap . ' ' . $v->jumlah_hasil)
            ->implode(', ');

        return redirect()->route('produksi.index')->with(
            'success',
            "Produksi berhasil disimpan ({$produksi->jumlah_hasil} pcs: {$ringkas})."
        );
    }

    /**
     * Kurangi stok produk kadaluwarsa untuk produk bervarian: varian yang dikurangi dipilih
     * lewat varian_id (nonaktif pun boleh, karena stok kadaluwarsa tetap harus dibuang).
     */
    private function kurangiStokVarian(Request $request, Produksi $produksi, Produk $produk)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
            'varian_id' => ['required', 'integer', Rule::exists('detail_produks', 'id')->where('produk_id', $produk->id)],
        ]);

        $jumlah = (int) $request->jumlah;
        $sisa = $produksi->sisa_belum_disesuaikan;

        if ($jumlah > $sisa) {
            return back()->with('error', "Jumlah melebihi sisa yang belum disesuaikan ({$sisa} pcs).");
        }

        $varian = DetailProduk::where('produk_id', $produk->id)->findOrFail($request->varian_id);

        if ($jumlah > (int) $varian->stok) {
            return back()->with('error', "Jumlah yang dikurangi melebihi stok varian {$varian->nama_lengkap} ({$varian->stok} pcs).");
        }

        DB::transaction(function () use ($jumlah, $varian, $produk, $produksi) {
            $v = DetailProduk::lockForUpdate()->findOrFail($varian->id);

            if ($jumlah > (int) $v->stok) {
                throw new Exception("Stok varian {$v->nama_lengkap} tidak mencukupi.");
            }

            $v->stok = (int) $v->stok - $jumlah;
            $v->save();

            $this->sinkronStokProdukDariVarian(Produk::lockForUpdate()->findOrFail($produk->id));

            $produksi->increment('jumlah_disesuaikan', $jumlah);
        });

        activity()
            ->causedBy(auth()->user())
            ->performedOn($produksi)
            ->withProperties([
                'jumlah_dikurangi' => $jumlah,
                'detail_produk_id' => $varian->id,
                'varian' => $varian->nama_lengkap,
            ])
            ->log('menyesuaikan stok produk kadaluwarsa');

        return back()->with('success', "Stok \"{$produk->nama_produk}\" varian {$varian->nama_lengkap} berhasil dikurangi {$jumlah} pcs.");
    }

    /** Stok total produk dihitung dari detail produk, jadi tidak ada yang perlu disalin ke tabel produks. */
    private function sinkronStokProdukDariVarian(Produk $produk): void
    {
        $produk->segarkanStok();
    }
}