<?php

namespace App\Http\Controllers;

use App\Models\Produksi;
use App\Models\Produk;
use App\Models\ProdukUkuran;
use App\Models\BahanBaku;
use App\Models\ProduksiBahanBaku;
use App\Models\ProduksiUkuran;
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

        return Produksi::with(['produk.produkUkurans.ukuran', 'user', 'detailBahan.bahanBaku.satuan', 'produksiUkurans.produkUkuran.ukuran'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('produk', function ($q) use ($search) {
                    $q->where('nama_produk', 'like', "%{$search}%");
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
                              ->orWhere('kode_produk', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('user.name', function ($q, $keyword) {
                        $q->whereHas('user', function ($qu) use ($keyword) {
                            $qu->where('name', 'like', "%{$keyword}%");
                        });
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
                    ->editColumn('tanggal', fn($item) => view('pages.produksi.partials.tanggal', compact('item'))->render())
                    ->addColumn('produk_html', fn($item) => view('pages.produksi.partials.produk', compact('item'))->render())
                    ->addColumn('hasil_html', fn($item) => view('pages.produksi.partials.hasil', compact('item'))->render())
                    ->editColumn('keterangan', fn($item) => view('pages.produksi.partials.keterangan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.produksi.partials.aksi', compact('item'))->render())
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
                'pages.produksi.index',
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
            $produk = Produk::with('produkUkurans.ukuran')->orderBy('nama_produk')->get();
            $bahanBaku = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();

            return view('pages.produksi.create', compact('produk', 'bahanBaku'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $produkDipilih = Produk::with('produkUkurans')->find($request->produk_id);
            $punyaUkuran = (bool) ($produkDipilih->punya_ukuran ?? false);

            // Mode dinamis dipakai kalau form ngirim array ukuran_hasil (nanti
            // dikirim oleh form Catat Produksi versi baru). Selama form-nya
            // belum diperbarui, request masih format lama (jumlah_besar/
            // jumlah_kecil) dan kode di bawah otomatis jalan seperti sebelumnya.
            $modeDinamis = $punyaUkuran && is_array($request->ukuran_hasil) && count(array_filter($request->ukuran_hasil, fn($v) => (int) $v > 0)) > 0;

            $request->validate([
                'tanggal' => 'required|date',
                'jam_produksi' => 'required|date_format:H:i',
                'produk_id' => 'required|exists:produks,id',
                'jumlah_hasil' => 'required|integer|min:1',
                'jumlah_besar' => [(!$modeDinamis && $punyaUkuran) ? 'required' : 'nullable', 'integer', 'min:0'],
                'jumlah_kecil' => [(!$modeDinamis && $punyaUkuran) ? 'required' : 'nullable', 'integer', 'min:0'],
                'kebutuhan_besar' => 'nullable|integer|min:0',
                'kebutuhan_kecil' => 'nullable|integer|min:0',
                'kebutuhan_produksi' => 'nullable|integer|min:0|lte:jumlah_hasil',
                'keterangan' => 'nullable|string',

                'ukuran_hasil' => 'nullable|array',
                'ukuran_hasil.*' => 'nullable|integer|min:0',
                'ukuran_kebutuhan' => 'nullable|array',
                'ukuran_kebutuhan.*' => 'nullable|integer|min:0',

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

            $rincianUkuranDinamis = []; // [produk_ukuran_id => ['hasil' => x, 'kebutuhan' => y]]

            if ($modeDinamis) {
                // Pastikan tiap produk_ukuran_id yang dikirim beneran milik produk ini.
                $idMilikProduk = $produkDipilih->produkUkurans->pluck('id')->all();

                $totalHasilDinamis = 0;
                foreach ($request->ukuran_hasil as $produkUkuranId => $hasil) {
                    $hasil = (int) $hasil;
                    if ($hasil <= 0) {
                        continue;
                    }
                    if (!in_array((int) $produkUkuranId, $idMilikProduk)) {
                        return back()->withInput()->with('error', 'Ada baris ukuran yang tidak valid untuk produk ini.');
                    }

                    $kebutuhan = (int) ($request->ukuran_kebutuhan[$produkUkuranId] ?? 0);
                    if ($kebutuhan > $hasil) {
                        return back()->withInput()->with('error', 'Kebutuhan tidak boleh melebihi jumlah hasil pada ukuran yang sama.');
                    }

                    $rincianUkuranDinamis[(int) $produkUkuranId] = [
                        'hasil' => $hasil,
                        'kebutuhan' => $kebutuhan,
                    ];
                    $totalHasilDinamis += $hasil;
                }

                if ($totalHasilDinamis !== (int) $request->jumlah_hasil) {
                    return back()->withInput()->with('error', 'Total jumlah hasil per ukuran harus sama dengan Jumlah Hasil Produksi.');
                }

                $jumlahBesar = 0;
                $jumlahKecil = 0;
                $kebutuhanBesar = 0;
                $kebutuhanKecil = 0;
                $kebutuhanTotal = array_sum(array_column($rincianUkuranDinamis, 'kebutuhan'));
            } elseif ($punyaUkuran) {
                if (((int) $request->jumlah_besar + (int) $request->jumlah_kecil) !== (int) $request->jumlah_hasil) {
                    return back()->withInput()->with('error', 'Jumlah Besar + Jumlah Kecil harus sama dengan Jumlah Hasil Produksi.');
                }

                $jumlahBesar = (int) $request->jumlah_besar;
                $jumlahKecil = (int) $request->jumlah_kecil;

                $kebutuhanBesar = (int) ($request->kebutuhan_besar ?? 0);
                $kebutuhanKecil = (int) ($request->kebutuhan_kecil ?? 0);
                $kebutuhanTotal = $kebutuhanBesar + $kebutuhanKecil;

                if ($kebutuhanBesar > $jumlahBesar) {
                    return back()->withInput()->with('error', 'Kebutuhan Besar tidak boleh melebihi Jumlah Besar hasil produksi.');
                }
                if ($kebutuhanKecil > $jumlahKecil) {
                    return back()->withInput()->with('error', 'Kebutuhan Kecil tidak boleh melebihi Jumlah Kecil hasil produksi.');
                }
            } else {
                // Produk tanpa ukuran: tidak ada rincian Besar/Kecil, cukup jumlah hasil & kebutuhan biasa.
                $jumlahBesar = 0;
                $jumlahKecil = 0;
                $kebutuhanBesar = 0;
                $kebutuhanKecil = 0;
                $kebutuhanTotal = (int) ($request->kebutuhan_produksi ?? 0);

                if ($kebutuhanTotal > (int) $request->jumlah_hasil) {
                    return back()->withInput()->with('error', 'Kebutuhan Produksi tidak boleh melebihi Jumlah Hasil Produksi.');
                }
            }

            $produksi = null;
            DB::transaction(function () use ($request, $kebutuhanTotal, $kebutuhanBesar, $kebutuhanKecil, $jumlahBesar, $jumlahKecil, $rincianUkuranDinamis, &$produksi) {

                $produksi = Produksi::create([
                    'tanggal' => $request->tanggal,
                    'jam_produksi' => $request->jam_produksi,
                    'produk_id' => $request->produk_id,
                    'jumlah_hasil' => $request->jumlah_hasil,
                    'jumlah_besar' => $jumlahBesar,
                    'jumlah_kecil' => $jumlahKecil,
                    'kebutuhan_produksi' => $kebutuhanTotal,
                    'kebutuhan_besar' => $kebutuhanBesar,
                    'kebutuhan_kecil' => $kebutuhanKecil,
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

                $produk = Produk::lockForUpdate()->findOrFail($request->produk_id);

                try {
                    if (!empty($rincianUkuranDinamis)) {
                        // Mode dinamis: tulis rincian per baris ukuran ke
                        // produksi_ukurans, dan sesuaikan stok langsung di
                        // baris produk_ukurans yang bersangkutan (bukan lewat
                        // pencocokan nama Besar/Kecil lagi).
                        foreach ($rincianUkuranDinamis as $produkUkuranId => $rincian) {
                            $baris = ProdukUkuran::lockForUpdate()->findOrFail($produkUkuranId);

                            $baris->stok += $rincian['hasil'];

                            if ($rincian['kebutuhan'] > 0) {
                                if ($baris->stok < $rincian['kebutuhan']) {
                                    throw new Exception("Stok {$baris->ukuran->nama_ukuran} untuk {$produk->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                                }
                                $baris->stok -= $rincian['kebutuhan'];
                            }

                            $baris->status_ketersediaan = $baris->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
                            $baris->save();

                            ProduksiUkuran::create([
                                'produksi_id' => $produksi->id,
                                'produk_ukuran_id' => $produkUkuranId,
                                'jumlah_hasil' => $rincian['hasil'],
                                'kebutuhan' => $rincian['kebutuhan'],
                            ]);

                            // Best-effort: kalau baris ini kebetulan namanya "Besar"
                            // atau "Kecil", sinkronkan juga ke kolom lama supaya
                            // tempat-tempat yang belum sempat dipindah tetap akurat.
                            $namaUkuran = $baris->ukuran->nama_ukuran ?? null;
                            if ($namaUkuran === 'Besar') {
                                $produk->stok_besar = $baris->stok;
                            } elseif ($namaUkuran === 'Kecil') {
                                $produk->stok_kecil = $baris->stok;
                            }
                        }
                        $produk->save();
                        $produk->refresh();
                    } elseif ($produk->punya_ukuran) {
                        // Produk punya varian ukuran: tambah & kurangi stok Besar/Kecil
                        // secara terpisah sesuai rincian hasil & kebutuhan produksi.
                        if ($jumlahBesar > 0) {
                            $produk->incrementStokUkuran('Besar', $jumlahBesar);
                        }
                        if ($jumlahKecil > 0) {
                            $produk->incrementStokUkuran('Kecil', $jumlahKecil);
                        }
                        $produk->refresh();

                        if ($kebutuhanBesar > 0) {
                            if ($produk->stok_besar < $kebutuhanBesar) {
                                throw new Exception("Stok Besar {$produk->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                            }
                            $produk->decrementStokUkuran('Besar', $kebutuhanBesar);
                        }
                        if ($kebutuhanKecil > 0) {
                            if ($produk->stok_kecil < $kebutuhanKecil) {
                                throw new Exception("Stok Kecil {$produk->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                            }
                            $produk->decrementStokUkuran('Kecil', $kebutuhanKecil);
                        }

                        $produk->refresh();
                    } else {
                        $produk->increment('stok', $request->jumlah_hasil);

                        if ($kebutuhanTotal > 0) {
                            if ($produk->stok < $kebutuhanTotal) {
                                throw new Exception("Stok {$produk->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                            }
                            $produk->decrement('stok', $kebutuhanTotal);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('Gagal memperbarui stok saat membuat tiket produksi', [
                        'produk_id' => $produk->id,
                        'jumlah_besar' => $jumlahBesar,
                        'jumlah_kecil' => $jumlahKecil,
                        'kebutuhan_besar' => $kebutuhanBesar,
                        'kebutuhan_kecil' => $kebutuhanKecil,
                        'rincian_ukuran_dinamis' => $rincianUkuranDinamis,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }

                if ($produk->stok > 0 && $produk->status_ketersediaan === 'perlu_diproduksi') {
                    $produk->update(['status_ketersediaan' => 'stok_ada']);
                }
            });

            event(new StatusProduksiUpdate($produksi));

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produksi)
                ->withProperties([
                    'produk_id' => $request->produk_id,
                    'jumlah_hasil' => $request->jumlah_hasil,
                    'jumlah_besar' => $jumlahBesar,
                    'jumlah_kecil' => $jumlahKecil,
                    'kebutuhan_produksi' => $kebutuhanTotal,
                    'rincian_ukuran_dinamis' => $rincianUkuranDinamis,
                    'bahan_baku_id' => $request->bahan_baku_id,
                    'jumlah_terpakai' => $request->jumlah_terpakai,
                ])
                ->log('menambahkan produksi');

            $keteranganJumlah = $punyaUkuran
                ? "{$produksi->jumlah_hasil} pcs: {$produksi->rincian_ukuran}"
                : "{$produksi->jumlah_hasil} pcs";

            return redirect()->route('produksi.index')->with(
                'success',
                "Produksi berhasil disimpan ({$keteranganJumlah})."
            );
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(Produksi $produksi)
    {
        try {
            $produksi->load(['produk', 'user', 'detailBahan.bahanBaku.satuan', 'produksiUkurans.produkUkuran.ukuran']);

            return view('pages.produksi.show', compact('produksi'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function label(Request $request, Produksi $produksi)
    {
        try {
            $produksi->load(['produk', 'produksiUkurans.produkUkuran.ukuran']);

            $rincianUkuran = $produksi->produksiUkurans
                ->filter(fn ($item) => $item->jumlah_hasil > 0)
                ->values();

            if ($rincianUkuran->isNotEmpty()) {
                // Produk punya varian ukuran: satu kelompok label per ukuran,
                // barcode & harga ngikutin produk_ukurans masing-masing.
                $labelItems = $rincianUkuran->map(function ($item) use ($produksi) {
                    return [
                        'kode_barcode' => $item->produkUkuran->kode_barcode ?? $produksi->produk->kode_produk,
                        'nama_ukuran' => optional($item->produkUkuran->ukuran)->nama_ukuran,
                        'harga_jual' => $item->produkUkuran->harga_jual ?? 0,
                        'jumlah' => (int) $item->jumlah_hasil,
                    ];
                });
            } else {
                // Produk tanpa ukuran: sama persis kayak sebelumnya.
                $jumlah = (int) $request->query('jumlah', $produksi->jumlah_hasil);
                $jumlah = max(1, min($jumlah, 500));

                $labelItems = collect([[
                    'kode_barcode' => $produksi->produk->kode_produk,
                    'nama_ukuran' => null,
                    'harga_jual' => $produksi->produk->harga_jual,
                    'jumlah' => $jumlah,
                ]]);
            }

            $totalLabel = (int) $labelItems->sum('jumlah');

            return view('pages.produksi.label', compact('produksi', 'labelItems', 'totalLabel'));
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
                'pages.produksi.pdf',
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
                'Rincian Ukuran',
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
                if ($item->detailBahan->isEmpty()) {
                    $sheet->fromArray([
                        $item->tanggal,
                        $item->jam_produksi,
                        $item->kadaluarsa_label,
                        $item->produk->nama_produk,
                        $item->jumlah_hasil,
                        $item->rincian_ukuran,
                        $item->user->name,
                        $item->keterangan,
                        '-',
                        '-',
                    ], null, "A{$row}");
                    $row++;
                } else {
                    foreach ($item->detailBahan as $detail) {
                        $sheet->fromArray([
                            $item->tanggal,
                            $item->jam_produksi,
                            $item->kadaluarsa_label,
                            $item->produk->nama_produk,
                            $item->jumlah_hasil,
                            $item->rincian_ukuran,
                            $item->user->name,
                            $item->keterangan,
                            $detail->bahanBaku->nama_bahan,
                            $detail->jumlah_terpakai . ' ' . ($detail->bahanBaku->satuan->nama ?? ''),
                        ], null, "A{$row}");
                        $row++;
                    }
                }
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

    public function kurangiStok(Request $request, Produksi $produksi)
    {
        try {
            $produk = $produksi->produk;

            // Validasi ukuran dinamis — dulu di-hardcode 'in:Besar,Kecil',
            // sekarang dicek terhadap daftar ukuran aktif produk ini
            // sendiri (bisa nama apapun yang didaftarin di menu Ukuran).
            $namaUkuranValid = $produk && $produk->punya_ukuran
                ? collect($produk->daftar_ukuran)->pluck('nama')->all()
                : [];

            $request->validate([
                'jumlah' => 'required|integer|min:1',
                'ukuran' => [
                    $produk && $produk->punya_ukuran ? 'required' : 'nullable',
                    Rule::in($namaUkuranValid),
                ],
            ]);

            $sisa = $produksi->sisa_belum_disesuaikan;

            if ($request->jumlah > $sisa) {
                return back()->with('error', "Jumlah melebihi sisa yang belum disesuaikan ({$sisa} pcs).");
            }

            $ukuran = $produk && $produk->punya_ukuran ? $request->ukuran : null;
            $stokTersediaUntukUkuran = $produk ? $produk->stokUntukUkuran($ukuran) : 0;

            if ($request->jumlah > $stokTersediaUntukUkuran) {
                return back()->with('error', 'Jumlah yang dikurangi melebihi stok produk saat ini.');
            }

            DB::transaction(function () use ($request, $produksi, $ukuran) {
                $produksi->produk->decrementStokUkuran($ukuran, $request->jumlah);
                $produksi->increment('jumlah_disesuaikan', $request->jumlah);
            });

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produksi)
                ->withProperties([
                    'jumlah_dikurangi' => $request->jumlah,
                    'ukuran' => $ukuran,
                ])
                ->log('menyesuaikan stok produk kadaluwarsa');

            return back()->with('success', "Stok produk \"{$produksi->produk->nama_produk}\" berhasil dikurangi {$request->jumlah} pcs.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}