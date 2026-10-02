<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Models\Ukuran;
use App\Models\ProdukUkuran;
use App\Models\VarianRasa;
use App\Models\ProdukVarianRasa;
use App\Models\ProdukUkuranRasa;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    private const FOTO_PATH = 'produk/images';

    private function pastikanBarisUkuranBesarKecil(Produk $produk): void
    {
        if (!$produk->punya_ukuran) {
            return;
        }

        $ukuranKecil = Ukuran::firstOrCreate(
            ['nama_ukuran' => 'Kecil'],
            ['urutan' => 1, 'status_aktif' => true]
        );

        $ukuranBesar = Ukuran::firstOrCreate(
            ['nama_ukuran' => 'Besar'],
            ['urutan' => 2, 'status_aktif' => true]
        );

        ProdukUkuran::firstOrCreate(
            ['produk_id' => $produk->id, 'ukuran_id' => $ukuranKecil->id],
            [
                'harga_modal' => $produk->harga_modal ?? 0,
                'harga_jual' => $produk->harga_kecil ?? 0,
                'stok' => $produk->stok_kecil ?? 0,
                'stok_minimum' => $produk->stok_minimum ?? 0,
                'status_ketersediaan' => ($produk->stok_kecil ?? 0) > 0 ? 'stok_ada' : 'perlu_diproduksi',
                'status_aktif' => true,
            ]
        );

        ProdukUkuran::firstOrCreate(
            ['produk_id' => $produk->id, 'ukuran_id' => $ukuranBesar->id],
            [
                'harga_modal' => $produk->harga_modal ?? 0,
                'harga_jual' => $produk->harga_besar ?? 0,
                'stok' => $produk->stok_besar ?? 0,
                'stok_minimum' => $produk->stok_minimum ?? 0,
                'status_ketersediaan' => ($produk->stok_besar ?? 0) > 0 ? 'stok_ada' : 'perlu_diproduksi',
                'status_aktif' => true,
            ]
        );
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Produk::with(['kategori', 'produkUkurans.ukuran', 'produkVarianRasas.varianRasa'])
                    ->when($request->filled('kategori_id'), function ($q) use ($request) {
                        $q->where('kategori_id', $request->kategori_id);
                    });

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->filterColumn('kategori.nama_kategori', function ($q, $keyword) {
                        $q->whereHas('kategori', function ($qk) use ($keyword) {
                            $qk->where('nama_kategori', 'like', "%{$keyword}%");
                        });
                    })
                    ->orderColumn('kategori.nama_kategori', function ($q, $order) {
                        $q->join('kategori_produks', 'produks.kategori_id', '=', 'kategori_produks.id')
                          ->orderBy('kategori_produks.nama_kategori', $order)
                          ->select('produks.*');
                    })
                    ->addColumn('foto', fn($item) => view('pages.produk.partials.foto', compact('item'))->render())
                    ->editColumn('nama_produk', fn($item) => view('pages.produk.partials.nama', compact('item'))->render())
                    ->addColumn('varian_rasa_html', fn($item) => view('pages.produk.partials.varian_rasa', compact('item'))->render())
                    ->addColumn('harga_jual_html', fn($item) => view('pages.produk.partials.harga', compact('item'))->render())
                    ->addColumn('stok_html', fn($item) => view('pages.produk.partials.stok', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.produk.partials.status', compact('item'))->render())
                    ->addColumn('ketersediaan_html', fn($item) => view('pages.produk.partials.ketersediaan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.produk.partials.aksi', compact('item'))->render())
                    ->rawColumns(['foto', 'nama_produk', 'varian_rasa_html', 'harga_jual_html', 'stok_html', 'status_html', 'ketersediaan_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data produk.'], 500);
            }
        }

        $kategori = KategoriProduk::orderBy('nama_kategori')->get();

        return view('pages.produk.index', compact('kategori'));
    }

    public function toggleStatus(Produk $produk)
    {
        try {
            $produk->update(['status_aktif' => !$produk->status_aktif]);
            return redirect()->back()->with('success', 'Status produk berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();
            $rawUkuran = Ukuran::aktif()->urut()->get();
            $rawVarianRasa = VarianRasa::aktif()->urut()->get();

            $masterUkuranList = $rawUkuran->map(fn ($u) => [
                'id' => $u->id,
                'nama' => $u->nama_ukuran,
            ])->values();

            $masterVarianRasaList = $rawVarianRasa->map(fn ($r) => [
                'id' => $r->id,
                'nama' => $r->nama_rasa,
                'gambar' => $r->gambar ? asset('storage/' . $r->gambar) : null,
            ])->values();

            $ukuranBaris = collect(old('ukuran_baris', []))
                ->map(function ($b) use ($rawUkuran) {
                    $master = $rawUkuran->firstWhere(
                        'id',
                        (int) ($b['ukuran_id'] ?? 0)
                    );

                    return [
                        'ukuran_id' => $b['ukuran_id'] ?? '',
                        'nama' => $master->nama_ukuran ?? '',
                        'harga_modal' => $b['harga_modal'] ?? '',
                        'harga_jual' => $b['harga_jual'] ?? '',
                        'stok' => $b['stok'] ?? '',
                        'stok_minimum' => $b['stok_minimum'] ?? '',
                        'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    ];
                })
                ->values();

            $rasaBaris = $this->barisRasaDariOld(old('rasa_baris', []), $rawVarianRasa);

            return view('pages.produk.create', compact(
                'kategori',
                'masterUkuranList',
                'ukuranBaris',
                'masterVarianRasaList',
                'rasaBaris'
            ));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function syncBarisUkuranDinamis(Produk $produk, $ukuranBaris): void
    {
        $ukuranIdDipakai = [];

        foreach ($ukuranBaris as $baris) {
            $stok = (int) ($baris['stok'] ?? 0);
            $ukuranId = (int) $baris['ukuran_id'];
            $ukuranIdDipakai[] = $ukuranId;

            ProdukUkuran::updateOrCreate(
                [
                    'produk_id' => $produk->id,
                    'ukuran_id' => $ukuranId,
                ],
                [
                    'harga_modal' => (float) ($baris['harga_modal'] ?? 0),
                    'harga_jual' => (float) ($baris['harga_jual'] ?? 0),
                    'stok' => $stok,
                    'stok_minimum' => (int) ($baris['stok_minimum'] ?? 0),
                    'status_ketersediaan' => $stok > 0 ? 'stok_ada' : 'perlu_diproduksi',
                    'status_aktif' => filter_var($baris['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]
            );
        }

        $produk->produkUkurans()
            ->whereNotIn('ukuran_id', $ukuranIdDipakai)
            ->delete();
    }

    /**
     * Normalisasi baris rasa dari request + hitung total stok.
     *
     * - Produk punya ukuran + rasa: stok diisi per (ukuran x rasa). Stok & stok minimum
     *   tiap ukuran = jumlah semua rasa, jadi baris ukuran ditimpa dengan total itu.
     * - Produk punya rasa saja: stok & stok minimum diisi per rasa.
     *
     * @return array [$ukuranBaris, $rasaBaris]
     */
    private function terapkanStokRasa($ukuranBaris, $rasaBaris, bool $punyaUkuran, bool $punyaRasa): array
    {
        if (!$punyaRasa) {
            return [$ukuranBaris, $rasaBaris];
        }

        if ($punyaUkuran) {
            $ukuranIds = $ukuranBaris->pluck('ukuran_id')->map(fn ($id) => (int) $id)->all();

            $rasaBaris = $rasaBaris->map(function ($b) use ($ukuranIds) {
                $perUkuran = [];
                foreach ($ukuranIds as $uid) {
                    $perUkuran[$uid] = [
                        'stok' => max(0, (int) data_get($b, "stok_ukuran.$uid.stok", 0)),
                        'stok_minimum' => max(0, (int) data_get($b, "stok_ukuran.$uid.stok_minimum", 0)),
                    ];
                }
                $b['stok_ukuran'] = $perUkuran;
                $b['stok'] = array_sum(array_column($perUkuran, 'stok'));
                $b['stok_minimum'] = array_sum(array_column($perUkuran, 'stok_minimum'));
                return $b;
            })->values();

            $ukuranBaris = $ukuranBaris->map(function ($u) use ($rasaBaris) {
                $uid = (int) $u['ukuran_id'];
                $u['stok'] = (int) $rasaBaris->sum(fn ($r) => $r['stok_ukuran'][$uid]['stok'] ?? 0);
                $u['stok_minimum'] = (int) $rasaBaris->sum(fn ($r) => $r['stok_ukuran'][$uid]['stok_minimum'] ?? 0);
                return $u;
            })->values();
        } else {
            $rasaBaris = $rasaBaris->map(function ($b) {
                $b['stok'] = max(0, (int) ($b['stok'] ?? 0));
                $b['stok_minimum'] = max(0, (int) ($b['stok_minimum'] ?? 0));
                $b['stok_ukuran'] = [];
                return $b;
            })->values();
        }

        return [$ukuranBaris, $rasaBaris];
    }

    private function syncBarisVarianRasaDinamis(Produk $produk, $rasaBaris, bool $punyaUkuran = false): void
    {
        $rasaIdDipakai = [];

        foreach ($rasaBaris as $baris) {
            $rasaId = (int) $baris['varian_rasa_id'];
            $rasaIdDipakai[] = $rasaId;
            $stok = (int) ($baris['stok'] ?? 0);

            ProdukVarianRasa::updateOrCreate(
                [
                    'produk_id' => $produk->id,
                    'varian_rasa_id' => $rasaId,
                ],
                [
                    'stok' => $stok,
                    'stok_minimum' => (int) ($baris['stok_minimum'] ?? 0),
                    'status_ketersediaan' => $stok > 0 ? 'stok_ada' : 'perlu_diproduksi',
                    'status_aktif' => filter_var($baris['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]
            );
        }

        $produk->produkVarianRasas()
            ->whereNotIn('varian_rasa_id', $rasaIdDipakai)
            ->delete();

        $this->syncStokUkuranRasa($produk, $rasaBaris, $punyaUkuran);
    }

    private function syncStokUkuranRasa(Produk $produk, $rasaBaris, bool $punyaUkuran): void
    {
        if (!$punyaUkuran) {
            $produk->produkUkuranRasas()->delete();
            return;
        }

        $kunciDipakai = [];

        foreach ($rasaBaris as $baris) {
            $rasaId = (int) $baris['varian_rasa_id'];

            foreach (($baris['stok_ukuran'] ?? []) as $ukuranId => $nilai) {
                $ukuranId = (int) $ukuranId;
                $stok = (int) ($nilai['stok'] ?? 0);
                $kunciDipakai[] = $ukuranId . '-' . $rasaId;

                ProdukUkuranRasa::updateOrCreate(
                    [
                        'produk_id' => $produk->id,
                        'ukuran_id' => $ukuranId,
                        'varian_rasa_id' => $rasaId,
                    ],
                    [
                        'stok' => $stok,
                        'stok_minimum' => (int) ($nilai['stok_minimum'] ?? 0),
                        'status_ketersediaan' => $stok > 0 ? 'stok_ada' : 'perlu_diproduksi',
                    ]
                );
            }
        }

        // Buang kombinasi ukuran x rasa yang sudah tidak dipakai.
        $produk->produkUkuranRasas()->get()
            ->filter(fn ($r) => !in_array($r->ukuran_id . '-' . $r->varian_rasa_id, $kunciDipakai, true))
            ->each->delete();
    }

    /** Bentuk data baris rasa untuk form (dari old input), termasuk stok per ukuran. */
    private function barisRasaDariOld($oldBaris, $rawVarianRasa)
    {
        return collect($oldBaris)->map(function ($b) use ($rawVarianRasa) {
            $master = $rawVarianRasa->firstWhere('id', (int) ($b['varian_rasa_id'] ?? 0));

            return [
                'varian_rasa_id' => $b['varian_rasa_id'] ?? '',
                'nama' => $master->nama_rasa ?? '',
                'gambar' => $master && $master->gambar ? asset('storage/' . $master->gambar) : null,
                'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'stok' => $b['stok'] ?? 0,
                'stok_minimum' => $b['stok_minimum'] ?? 0,
                'stok_map' => collect($b['stok_ukuran'] ?? [])
                    ->map(fn ($v) => [
                        'stok' => $v['stok'] ?? 0,
                        'stok_minimum' => $v['stok_minimum'] ?? 0,
                    ])->all(),
            ];
        })->values();
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kategori_id' => 'required|exists:kategori_produks,id',
                'kode_produk' => 'required|max:50|unique:produks,kode_produk',
                'nama_produk' => 'required|max:100',
                'deskripsi' => 'nullable|string|max:1000',
                'harga_jual' => 'nullable|required_if:punya_ukuran,0|numeric|min:0',
                'punya_ukuran' => 'nullable|boolean',
                'punya_varian_rasa' => 'nullable|boolean',
                'harga_modal' => 'nullable|required_if:punya_ukuran,0|numeric|min:0',
                'stok' => [
                    'nullable',
                    Rule::requiredIf(fn () => !$request->boolean('punya_ukuran') && !$request->boolean('punya_varian_rasa')),
                    'numeric',
                    'min:0',
                ],
                'stok_minimum' => 'nullable|numeric|min:0',
                'status_aktif' => 'required|boolean',
                'status_ketersediaan' => [
                    'required',
                    Rule::in(['stok_ada', 'perlu_diproduksi']),
                ],
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

                'ukuran_baris' => 'nullable|array|required_if:punya_ukuran,1',
                'ukuran_baris.*.ukuran_id' => 'required_with:ukuran_baris|exists:ukurans,id|distinct',
                'ukuran_baris.*.harga_modal' => 'required_with:ukuran_baris|numeric|min:0',
                'ukuran_baris.*.harga_jual' => 'required_with:ukuran_baris|numeric|min:0',
                // Kalau produk punya rasa, stok ukuran dihitung dari stok rasa (bukan diisi di sini).
                'ukuran_baris.*.stok' => 'nullable|integer|min:0',
                'ukuran_baris.*.stok_minimum' => 'nullable|integer|min:0',

                'rasa_baris' => 'nullable|array|required_if:punya_varian_rasa,1',
                'rasa_baris.*.varian_rasa_id' => 'required_with:rasa_baris|exists:varian_rasas,id|distinct',
                'rasa_baris.*.stok' => 'nullable|integer|min:0',
                'rasa_baris.*.stok_minimum' => 'nullable|integer|min:0',
                'rasa_baris.*.stok_ukuran' => 'nullable|array',
                'rasa_baris.*.stok_ukuran.*.stok' => 'nullable|integer|min:0',
                'rasa_baris.*.stok_ukuran.*.stok_minimum' => 'nullable|integer|min:0',
            ], [
                'ukuran_baris.required_if' => 'Tambahkan minimal 1 baris ukuran.',
                'ukuran_baris.*.ukuran_id.distinct' => 'Satu ukuran cuma boleh ditambahkan sekali.',
                'rasa_baris.required_if' => 'Tambahkan minimal 1 baris varian rasa.',
                'rasa_baris.*.varian_rasa_id.distinct' => 'Satu varian rasa cuma boleh ditambahkan sekali.',
            ]);

            $ukuranBaris = collect($request->input('ukuran_baris', []))
                ->filter(fn ($baris) => !empty($baris['ukuran_id']))
                ->values();

            $rasaBaris = collect($request->input('rasa_baris', []))
                ->filter(fn ($baris) => !empty($baris['varian_rasa_id']))
                ->values();

            $validated['punya_ukuran'] = $request->has('punya_ukuran') ? (bool) $request->punya_ukuran : false;
            $validated['punya_varian_rasa'] = $request->has('punya_varian_rasa') ? (bool) $request->punya_varian_rasa : false;

            [$ukuranBaris, $rasaBaris] = $this->terapkanStokRasa(
                $ukuranBaris,
                $rasaBaris,
                $validated['punya_ukuran'],
                $validated['punya_varian_rasa'] && $rasaBaris->isNotEmpty()
            );

            if ($validated['punya_ukuran']) {
                $validated['harga_jual'] = 0;
                $validated['harga_modal'] = 0;
                $validated['stok'] = (int) $ukuranBaris->sum(fn ($b) => (int) ($b['stok'] ?? 0));
                $validated['stok_minimum'] = 0;
                $validated['harga_besar'] = null;
                $validated['harga_kecil'] = null;
                $validated['stok_besar'] = 0;
                $validated['stok_kecil'] = 0;
            } else {
                if ($validated['punya_varian_rasa'] && $rasaBaris->isNotEmpty()) {
                    $validated['stok'] = (int) $rasaBaris->sum('stok');
                    $validated['stok_minimum'] = (int) $rasaBaris->sum('stok_minimum');
                } else {
                    $validated['stok'] = (int) ($validated['stok'] ?? 0);
                    $validated['stok_minimum'] = (int) ($validated['stok_minimum'] ?? 0);
                }
                $validated['harga_jual'] = (float) ($validated['harga_jual'] ?? 0);
                $validated['harga_modal'] = (float) ($validated['harga_modal'] ?? 0);
                $validated['harga_besar'] = null;
                $validated['harga_kecil'] = null;
                $validated['stok_besar'] = 0;
                $validated['stok_kecil'] = 0;
            }

            $validated['status_ketersediaan'] = ($validated['stok'] ?? 0) <= 0
                ? 'perlu_diproduksi'
                : 'stok_ada';

            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('produk/' . $validated['kode_produk'], 'public');
            }

            unset($validated['ukuran_baris'], $validated['rasa_baris']);

            $produk = Produk::create($validated);

            if ($validated['punya_ukuran']) {
                if ($ukuranBaris->isNotEmpty()) {
                    $this->syncBarisUkuranDinamis($produk, $ukuranBaris);
                } else {
                     $this->pastikanBarisUkuranBesarKecil($produk);
                }
            }

            if ($validated['punya_varian_rasa'] && $rasaBaris->isNotEmpty()) {
                $this->syncBarisVarianRasaDinamis($produk, $rasaBaris, $validated['punya_ukuran']);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produk)
                ->withProperties([
                    'kode_produk' => $validated['kode_produk'],
                    'nama_produk' => $validated['nama_produk'],
                    'kategori_id' => $validated['kategori_id'],
                    'harga_jual' => $validated['harga_jual'],
                    'stok' => $validated['stok'],
                    'jumlah_ukuran' => $ukuranBaris->count(),
                    'jumlah_rasa' => $rasaBaris->count(),
                ])
                ->log('menambahkan produk');

            return redirect()
                ->route('produk.index')
                ->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(Produk $produk)
    {
        try {
            $produk->load(['kategori', 'produkUkurans.ukuran', 'produkVarianRasas.varianRasa', 'produkUkuranRasas']);

            return view('pages.produk.show', compact('produk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Produk $produk)
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();
            $rawUkuran = Ukuran::aktif()->urut()->get();
            $rawVarianRasa = VarianRasa::aktif()->urut()->get();

            $masterUkuranList = $rawUkuran->map(fn ($u) => [
                'id' => $u->id,
                'nama' => $u->nama_ukuran,
            ])->values();

            $masterVarianRasaList = $rawVarianRasa->map(fn ($r) => [
                'id' => $r->id,
                'nama' => $r->nama_rasa,
                'gambar' => $r->gambar ? asset('storage/' . $r->gambar) : null,
            ])->values();

            $produk->load(['produkUkurans.ukuran', 'produkVarianRasas.varianRasa']);

            if (old('ukuran_baris') !== null) {
                 $ukuranBaris = collect(old('ukuran_baris', []))
                    ->map(function ($b) use ($rawUkuran) {
                        $master = $rawUkuran->firstWhere(
                            'id',
                            (int) ($b['ukuran_id'] ?? 0)
                        );

                        return [
                            'ukuran_id' => $b['ukuran_id'] ?? '',
                            'nama' => $master->nama_ukuran ?? '',
                            'harga_modal' => $b['harga_modal'] ?? '',
                            'harga_jual' => $b['harga_jual'] ?? '',
                            'stok' => $b['stok'] ?? '',
                            'stok_minimum' => $b['stok_minimum'] ?? '',
                            'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                        ];
                    })
                    ->values();
            } else {
                $ukuranBaris = $produk->produkUkurans
                    ->map(fn ($pu) => [
                        'ukuran_id' => $pu->ukuran_id,
                        'nama' => $pu->ukuran->nama_ukuran ?? '',
                        'harga_modal' => (int) $pu->harga_modal,
                        'harga_jual' => (int) $pu->harga_jual,
                        'stok' => (int) $pu->stok,
                        'stok_minimum' => (int) $pu->stok_minimum,
                        'status_aktif' => (bool) $pu->status_aktif,
                    ])
                    ->values();
            }

            if (old('rasa_baris') !== null) {
                $rasaBaris = $this->barisRasaDariOld(old('rasa_baris', []), $rawVarianRasa);
            } else {
                $stokUkuranRasa = $produk->produkUkuranRasas()->get()->groupBy('varian_rasa_id');

                $rasaBaris = $produk->produkVarianRasas
                    ->map(fn ($pr) => [
                        'varian_rasa_id' => $pr->varian_rasa_id,
                        'nama' => $pr->varianRasa->nama_rasa ?? '',
                        'gambar' => $pr->varianRasa && $pr->varianRasa->gambar ? asset('storage/' . $pr->varianRasa->gambar) : null,
                        'status_aktif' => (bool) $pr->status_aktif,
                        'stok' => (int) $pr->stok,
                        'stok_minimum' => (int) $pr->stok_minimum,
                        'stok_map' => ($stokUkuranRasa->get($pr->varian_rasa_id) ?? collect())
                            ->mapWithKeys(fn ($r) => [$r->ukuran_id => [
                                'stok' => (int) $r->stok,
                                'stok_minimum' => (int) $r->stok_minimum,
                            ]])->all(),
                    ])
                    ->values();
            }

            return view('pages.produk.edit', compact(
                'produk',
                'kategori',
                'masterUkuranList',
                'ukuranBaris',
                'masterVarianRasaList',
                'rasaBaris'
            ));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Produk $produk)
    {
        try {
            $validated = $request->validate([
                'kategori_id' => 'required|exists:kategori_produks,id',
                'kode_produk' => [
                    'required',
                    'max:50',
                    Rule::unique('produks', 'kode_produk')->ignore($produk->id),
                ],
                'nama_produk' => 'required|max:100',
                'deskripsi' => 'nullable|string|max:1000',
                'harga_jual' => 'nullable|required_if:punya_ukuran,0|numeric|min:0',
                'punya_ukuran' => 'nullable|boolean',
                'punya_varian_rasa' => 'nullable|boolean',
                'harga_modal' => 'nullable|required_if:punya_ukuran,0|numeric|min:0',
                'stok' => [
                    'nullable',
                    Rule::requiredIf(fn () => !$request->boolean('punya_ukuran') && !$request->boolean('punya_varian_rasa')),
                    'numeric',
                    'min:0',
                ],
                'stok_minimum' => 'nullable|numeric|min:0',
                'status_aktif' => 'required|boolean',
                'status_ketersediaan' => [
                    'required',
                    Rule::in(['stok_ada', 'perlu_diproduksi']),
                ],
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

                'ukuran_baris' => 'nullable|array|required_if:punya_ukuran,1',
                'ukuran_baris.*.ukuran_id' => 'required_with:ukuran_baris|exists:ukurans,id|distinct',
                'ukuran_baris.*.harga_modal' => 'required_with:ukuran_baris|numeric|min:0',
                'ukuran_baris.*.harga_jual' => 'required_with:ukuran_baris|numeric|min:0',
                // Kalau produk punya rasa, stok ukuran dihitung dari stok rasa (bukan diisi di sini).
                'ukuran_baris.*.stok' => 'nullable|integer|min:0',
                'ukuran_baris.*.stok_minimum' => 'nullable|integer|min:0',

                'rasa_baris' => 'nullable|array|required_if:punya_varian_rasa,1',
                'rasa_baris.*.varian_rasa_id' => 'required_with:rasa_baris|exists:varian_rasas,id|distinct',
                'rasa_baris.*.stok' => 'nullable|integer|min:0',
                'rasa_baris.*.stok_minimum' => 'nullable|integer|min:0',
                'rasa_baris.*.stok_ukuran' => 'nullable|array',
                'rasa_baris.*.stok_ukuran.*.stok' => 'nullable|integer|min:0',
                'rasa_baris.*.stok_ukuran.*.stok_minimum' => 'nullable|integer|min:0',
            ], [
                'ukuran_baris.required_if' => 'Tambahkan minimal 1 baris ukuran.',
                'ukuran_baris.*.ukuran_id.distinct' => 'Satu ukuran cuma boleh ditambahkan sekali.',
                'rasa_baris.required_if' => 'Tambahkan minimal 1 baris varian rasa.',
                'rasa_baris.*.varian_rasa_id.distinct' => 'Satu varian rasa cuma boleh ditambahkan sekali.',
            ]);

            $ukuranBaris = collect($request->input('ukuran_baris', []))
                ->filter(fn ($baris) => !empty($baris['ukuran_id']))
                ->values();

            $rasaBaris = collect($request->input('rasa_baris', []))
                ->filter(fn ($baris) => !empty($baris['varian_rasa_id']))
                ->values();

            $oldKode = $produk->kode_produk;
            $newKode = $validated['kode_produk'];

            if ($request->hasFile('foto')) {
                if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->deleteDirectory('produk/' . $oldKode);
                }

                $validated['foto'] = $request
                    ->file('foto')
                    ->store('produk/' . $newKode, 'public');
            } elseif ($oldKode !== $newKode && $produk->foto) {
                $oldFolder = 'produk/' . $oldKode;
                $newFolder = 'produk/' . $newKode;

                if (Storage::disk('public')->exists($oldPath = $produk->foto)) {
                    $filename = basename($oldPath);
                    $newPath = $newFolder . '/' . $filename;
                    Storage::disk('public')->move($oldPath, $newPath);
                    $validated['foto'] = $newPath;
                }
                Storage::disk('public')->deleteDirectory($oldFolder);
            }

            $validated['punya_ukuran'] = $request->has('punya_ukuran') ? (bool) $request->punya_ukuran : false;
            $validated['punya_varian_rasa'] = $request->has('punya_varian_rasa') ? (bool) $request->punya_varian_rasa : false;

            [$ukuranBaris, $rasaBaris] = $this->terapkanStokRasa(
                $ukuranBaris,
                $rasaBaris,
                $validated['punya_ukuran'],
                $validated['punya_varian_rasa'] && $rasaBaris->isNotEmpty()
            );

            if ($validated['punya_ukuran']) {
                $validated['harga_jual'] = 0;
                $validated['harga_modal'] = 0;
                $validated['stok'] = (int) $ukuranBaris->sum(fn ($b) => (int) ($b['stok'] ?? 0));
                $validated['stok_minimum'] = 0;
            } else {
                if ($validated['punya_varian_rasa'] && $rasaBaris->isNotEmpty()) {
                    $validated['stok'] = (int) $rasaBaris->sum('stok');
                    $validated['stok_minimum'] = (int) $rasaBaris->sum('stok_minimum');
                } else {
                    $validated['stok'] = (int) ($validated['stok'] ?? 0);
                    $validated['stok_minimum'] = (int) ($validated['stok_minimum'] ?? 0);
                }
                $validated['harga_jual'] = (float) ($validated['harga_jual'] ?? 0);
                $validated['harga_modal'] = (float) ($validated['harga_modal'] ?? 0);
            }

            unset($validated['ukuran_baris'], $validated['rasa_baris']);

            $validated['status_ketersediaan'] = ($validated['stok'] ?? 0) <= 0
                ? 'perlu_diproduksi'
                : 'stok_ada';

            $produk->update($validated);

            // Perilaku asli dipertahankan: melepas centang "punya ukuran" TIDAK menghapus
            // baris produk_ukurans. Menghapusnya ikut menghapus riwayat produksi per ukuran
            // (produksi_ukurans cascade ke produk_ukurans).
            if ($validated['punya_ukuran'] && $ukuranBaris->isNotEmpty()) {
                $this->syncBarisUkuranDinamis($produk, $ukuranBaris);
            }

            if ($validated['punya_varian_rasa']) {
                if ($rasaBaris->isNotEmpty()) {
                    $this->syncBarisVarianRasaDinamis($produk, $rasaBaris, $validated['punya_ukuran']);
                }
            } else {
                $produk->produkVarianRasas()->delete();
                $produk->produkUkuranRasas()->delete();
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produk)
                ->withProperties([
                    'kode_produk' => $validated['kode_produk'],
                    'nama_produk' => $validated['nama_produk'],
                    'kategori_id' => $validated['kategori_id'],
                    'harga_jual' => $validated['harga_jual'],
                    'stok' => $validated['stok'],
                    'jumlah_ukuran' => $ukuranBaris->count(),
                    'jumlah_rasa' => $rasaBaris->count(),
                ])
                ->log('memperbarui produk');

            return redirect()
                ->route('produk.index')
                ->with('success', 'Produk berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Produk $produk)
    {
        try {
            // Produk yang sudah pernah dicatat di Riwayat Produksi gak boleh
            // dihapus (FK produksis.produk_id di-restrict, sengaja biar
            // histori produksi lama gak ikut ilang/rusak). Cek dulu di sini
            // biar errornya jelas buat user, bukan raw SQL error, dan biar
            // activity log-nya gak kecatat "menghapus produk" kalau ternyata
            // penghapusannya gagal.
            if ($produk->produksi()->exists()) {
                return back()->with(
                    'error',
                    "Produk \"{$produk->nama_produk}\" tidak bisa dihapus karena sudah punya riwayat produksi. Nonaktifkan produk ini saja kalau tidak mau dipakai lagi."
                );
            }

            DB::transaction(function () use ($produk) {
                if ($produk->kode_produk) {
                    Storage::disk('public')->deleteDirectory('produk/' . $produk->kode_produk);
                } elseif ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->delete($produk->foto);
                }

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $produk->kode_produk,
                        'nama_produk' => $produk->nama_produk,
                        'kategori_id' => $produk->kategori_id,
                        'harga_jual' => $produk->harga_jual,
                        'stok' => $produk->stok,
                    ])
                    ->log('menghapus produk');

                $produk->delete();
            });

            return redirect()
                ->route('produk.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}