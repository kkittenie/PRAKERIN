<?php

namespace App\Http\Controllers;

use App\Models\Produksi;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\ProduksiBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class ProduksiController extends Controller
{
    /**
     * Resolve periode & tanggal referensi dari request.
     * Dipakai bareng oleh index(), pdf(), dan excel() biar filternya selalu selaras.
     */
    private function resolvePeriode(Request $request): array
    {
        $periode = $request->periode ?? 'harian';
        $tanggalRefInput = $request->tanggal_ref;

        switch ($periode) {
            case 'semua':
                // Tidak ada filter tanggal sama sekali, tanggalRef cuma dipakai
                // sebagai nilai default kalau user beralih ke periode lain di form.
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

    /**
     * Query produksi yang sudah difilter sesuai periode, pencarian, & status kadaluarsa.
     * Dipakai bareng oleh index(), pdf(), dan excel().
     */
    private function filteredProduksiQuery(Request $request, string $periode, Carbon $tanggalRef)
    {
        $search = $request->search;
        $status = $request->status ?? 'semua';
        $now = Carbon::now();

        // Ekspresi SQL untuk menentukan status kadaluarsa langsung di query,
        // karena kadaluarsa bisa berupa tanggal tetap ATAU jam setelah produksi.
        $kadaluarsaExpr = "
            CASE
                WHEN kadaluarsa_tipe = 'tanggal' THEN kadaluarsa_tanggal < ?
                WHEN kadaluarsa_tipe = 'jam' THEN DATE_ADD(CONCAT(tanggal, ' ', jam_produksi), INTERVAL kadaluarsa_jam HOUR) < ?
                ELSE 0
            END
        ";

        return Produksi::with(['produk', 'user', 'detailBahan.bahanBaku.satuan'])
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

    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status ?? 'semua';

        [$periode, $tanggalRef] = $this->resolvePeriode($request);

        $produksi = $this->filteredProduksiQuery($request, $periode, $tanggalRef)
            ->latest()
            ->paginate(10);

        $produksiKadaluarsa = Produksi::with('produk')
            ->whereColumn('jumlah_disesuaikan', '<', 'jumlah_hasil')
            ->get()
            ->filter(fn ($item) => $item->is_kadaluarsa);

        return view(
            'pages.produksi.index',
            compact('produksi', 'search', 'status', 'periode', 'tanggalRef', 'produksiKadaluarsa')
        );
    }

    public function create()
    {
        $produk = Produk::orderBy('nama_produk')->get();
        $bahanBaku = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();

        return view('pages.produksi.create', compact('produk', 'bahanBaku'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_produksi' => 'required|date_format:H:i',
            'produk_id' => 'required|exists:produks,id',
            'jumlah_hasil' => 'required|integer|min:1',
            'kebutuhan_produksi' => 'nullable|integer|min:0|lte:jumlah_hasil',
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

        try {
            $produksi = null;
            DB::transaction(function () use ($request, &$produksi) {
                $kebutuhanProduksi = (int) ($request->kebutuhan_produksi ?? 0);

                $produksi = Produksi::create([
                    'tanggal' => $request->tanggal,
                    'jam_produksi' => $request->jam_produksi,
                    'produk_id' => $request->produk_id,
                    'jumlah_hasil' => $request->jumlah_hasil,
                    'kebutuhan_produksi' => $kebutuhanProduksi,
                    'user_id' => Auth::id(),
                    'keterangan' => $request->keterangan,
                    'kadaluarsa_tipe' => $request->kadaluarsa_tipe,
                    'kadaluarsa_tanggal' => $request->kadaluarsa_tipe === 'tanggal' ? $request->kadaluarsa_tanggal : null,
                    'kadaluarsa_jam' => $request->kadaluarsa_tipe === 'jam' ? $request->kadaluarsa_jam : null,
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
                $produk->increment('stok', $request->jumlah_hasil);

                if ($kebutuhanProduksi > 0) {
                    if ($produk->stok < $kebutuhanProduksi) {
                        throw new Exception("Stok {$produk->nama_produk} tidak mencukupi untuk kebutuhan produksi.");
                    }

                    $produk->decrement('stok', $kebutuhanProduksi);
                }
            });

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produksi)
                ->withProperties([
                    'produk_id' => $request->produk_id,
                    'jumlah_hasil' => $request->jumlah_hasil,
                    'kebutuhan_produksi' => $request->kebutuhan_produksi ?? 0,
                    'bahan_baku_id' => $request->bahan_baku_id,
                    'jumlah_terpakai' => $request->jumlah_terpakai,
                ])
                ->log('menambahkan produksi');

            return redirect()->route('produksi.index')->with('success', 'Produksi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Produksi $produksi)
    {
        $produksi->load(['produk', 'user', 'detailBahan.bahanBaku.satuan']);

        return view('pages.produksi.show', compact('produksi'));
    }

    public function pdf(Request $request)
    {
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
    }

    public function excel(Request $request)
    {
        [$periode, $tanggalRef] = $this->resolvePeriode($request);

        $produksi = $this->filteredProduksiQuery($request, $periode, $tanggalRef)
            ->orderBy('tanggal')
            ->orderBy('jam_produksi')
            ->get();

        $filename = 'laporan-produksi-' . now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($produksi) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Tanggal Produksi', 'Jam Produksi', 'Kadaluarsa', 'Produk',
                'Jumlah Hasil', 'Dibuat Oleh', 'Keterangan', 'Nama Bahan', 'Jumlah Dipakai',
            ]);

            foreach ($produksi as $item) {
                if ($item->detailBahan->isEmpty()) {
                    fputcsv($file, [
                        $item->tanggal, $item->jam_produksi, $item->kadaluarsa_label,
                        $item->produk->nama_produk, $item->jumlah_hasil, $item->user->name,
                        $item->keterangan, '-', '-',
                    ]);
                } else {
                    foreach ($item->detailBahan as $detail) {
                        fputcsv($file, [
                            $item->tanggal, $item->jam_produksi, $item->kadaluarsa_label,
                            $item->produk->nama_produk, $item->jumlah_hasil, $item->user->name,
                            $item->keterangan, $detail->bahanBaku->nama_bahan,
                            $detail->jumlah_terpakai . ' ' . ($detail->bahanBaku->satuan->nama ?? ''),
                        ]);
                    }
                }
            }

            fclose($file);
        }, 200, $headers);
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
        return back()->with('error', 'Data produksi tidak dapat dihapus.');
    }

    public function kurangiStok(Request $request, Produksi $produksi)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        $sisa = $produksi->sisa_belum_disesuaikan;

        if ($request->jumlah > $sisa) {
            return back()->with('error', "Jumlah melebihi sisa yang belum disesuaikan ({$sisa} pcs).");
        }

        if ($request->jumlah > $produksi->produk->stok) {
            return back()->with('error', 'Jumlah yang dikurangi melebihi stok produk saat ini.');
        }

        DB::transaction(function () use ($request, $produksi) {
            $produksi->produk->decrement('stok', $request->jumlah);
            $produksi->increment('jumlah_disesuaikan', $request->jumlah);
        });

        activity()
            ->causedBy(auth()->user())
            ->performedOn($produksi)
            ->withProperties(['jumlah_dikurangi' => $request->jumlah])
            ->log('menyesuaikan stok produk kadaluarsa');

        return back()->with('success', "Stok produk \"{$produksi->produk->nama_produk}\" berhasil dikurangi {$request->jumlah} pcs.");
    }
}