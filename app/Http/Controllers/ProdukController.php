<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Models\Ukuran;
use App\Models\ProdukUkuran;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    private const FOTO_PATH = 'produk/images';

    /**
     * Pas produk baru dibuat dengan "Punya Ukuran" aktif, form ini masih
     * minta diisi Harga/Stok Besar & Kecil (kolom lama) — tapi dulu gak ada
     * yang otomatis bikin baris di produk_ukurans dari nilai itu. Akibatnya
     * produk yang baru dibuat nongol "Belum ada ukuran aktif" di kasir &
     * mobile sampai admin balik ke halaman Edit buat nambahin manual.
     *
     * Method ini nutup celah itu: begitu produk baru dibuat, otomatis
     * dibikinin 2 baris ukuran ("Kecil" & "Besar") dari nilai yang barusan
     * diisi di form, persis logika yang dipakai migration backfill buat
     * data lama. Pakai firstOrCreate supaya SELALU aman dipanggil ulang
     * (kalau baris itu udah ada / udah dikustomisasi admin lewat panel
     * Ukuran Dinamis, gak bakal ketimpa/direset).
     */
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
                $query = Produk::with(['kategori', 'produkUkurans.ukuran'])
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
                    ->addColumn('harga_jual_html', fn($item) => view('pages.produk.partials.harga', compact('item'))->render())
                    ->addColumn('stok_html', fn($item) => view('pages.produk.partials.stok', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.produk.partials.status', compact('item'))->render())
                    ->addColumn('ketersediaan_html', fn($item) => view('pages.produk.partials.ketersediaan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.produk.partials.aksi', compact('item'))->render())
                    ->rawColumns(['foto', 'nama_produk', 'harga_jual_html', 'stok_html', 'status_html', 'ketersediaan_html', 'aksi'])
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
            $daftarUkuran = Ukuran::aktif()->urut()->get();

            $ukuranBaris = collect(old('ukuran_baris', []))
                ->map(function ($b) use ($daftarUkuran) {
                    $master = $daftarUkuran->firstWhere(
                        'id',
                        (int) ($b['ukuran_id'] ?? 0)
                    );

                    return [
                        'ukuran_id' => $b['ukuran_id'] ?? '',
                        'nama' => $master->nama_ukuran ?? '',
                        'harga_modal' => $b['harga_modal'] ?? '',
                        'harga_jual' => $b['harga_jual'] ?? '',
                        'stok' => $b['stok'] ?? '',
                        'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    ];
                })
                ->values();

            return view('pages.produk.create', compact(
                'kategori',
                'daftarUkuran',
                'ukuranBaris'
            ));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Simpan/sinkronisasi baris produk_ukurans dari input repeater dinamis
     * (ukuran_baris[]) di form Tambah & Edit Produk. Dipakai bareng di
     * store() (produk baru, belum ada baris lama) dan update() (produk
     * existing — baris yang udah gak ada di draft ikut dihapus, biar form
     * Edit persis kaya form Tambah: apa yang keliatan di tabel = itu yang
     * kesimpen).
     */
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
                    'stok_minimum' => (int) ($produk->stok_minimum ?? 0),
                    'status_ketersediaan' => $stok > 0 ? 'stok_ada' : 'perlu_diproduksi',
                    'status_aktif' => filter_var($baris['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]
            );
        }

        // Baris ukuran lama yang udah gak ada di draft (dihapus admin di
        // form) ikut dibuang, biar konsisten sama apa yang keliatan di form.
        $produk->produkUkurans()
            ->whereNotIn('ukuran_id', $ukuranIdDipakai)
            ->delete();
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
                'harga_modal' => 'nullable|required_if:punya_ukuran,0|numeric|min:0',
                'stok' => 'nullable|required_if:punya_ukuran,0|integer|min:0',
                'stok_minimum' => 'required|integer|min:0',
                'status_aktif' => 'required|boolean',
                'status_ketersediaan' => [
                    'required',
                    Rule::in(['stok_ada', 'perlu_diproduksi']),
                ],
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

                // Mode dinamis: dipakai kalau "Punya Ukuran" dicentang. Setiap
                // baris = satu ukuran dari master data Ukuran, bukan lagi
                // hardcode Besar/Kecil. Harga modal ikut per-baris juga.
                'ukuran_baris' => 'nullable|array|required_if:punya_ukuran,1',
                'ukuran_baris.*.ukuran_id' => 'required_with:ukuran_baris|exists:ukurans,id|distinct',
                'ukuran_baris.*.harga_modal' => 'required_with:ukuran_baris|numeric|min:0',
                'ukuran_baris.*.harga_jual' => 'required_with:ukuran_baris|numeric|min:0',
                'ukuran_baris.*.stok' => 'required_with:ukuran_baris|integer|min:0',
            ], [
                'ukuran_baris.required_if' => 'Tambahkan minimal 1 baris ukuran.',
                'ukuran_baris.*.ukuran_id.distinct' => 'Satu ukuran cuma boleh ditambahkan sekali.',
            ]);

            $ukuranBaris = collect($request->input('ukuran_baris', []))
                ->filter(fn ($baris) => !empty($baris['ukuran_id']))
                ->values();

            $validated['punya_ukuran'] = $request->has('punya_ukuran') ? (bool) $request->punya_ukuran : false;
            if ($validated['punya_ukuran']) {
                $validated['harga_jual'] = 0;
                $validated['harga_modal'] = 0;
                $validated['stok'] = (int) $ukuranBaris->sum(fn ($b) => (int) ($b['stok'] ?? 0));
                // Sumber kebenaran harga & stok sekarang full di produk_ukurans;
                // kolom lama ini cuma tersisa untuk data lama, jadi dikosongkan.
                // PENTING: stok_besar/stok_kecil di kolom produks itu NOT NULL
                // (default 0) — diisi null di sini bikin insert selalu gagal
                // kena constraint DB tiap kali "Punya Ukuran" dicentang. Pakai
                // 0, bukan null.
                $validated['harga_besar'] = null;
                $validated['harga_kecil'] = null;
                $validated['stok_besar'] = 0;
                $validated['stok_kecil'] = 0;
            } else {
                $validated['harga_besar'] = null;
                $validated['harga_kecil'] = null;
                $validated['stok_besar'] = 0;
                $validated['stok_kecil'] = 0;
            }

            $validated['status_ketersediaan'] = $validated['stok'] <= 0
                ? 'perlu_diproduksi'
                : 'stok_ada';

            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store(self::FOTO_PATH, 'public');
            }

            unset($validated['ukuran_baris']);

            $produk = Produk::create($validated);

            if ($validated['punya_ukuran']) {
                if ($ukuranBaris->isNotEmpty()) {
                    $this->syncBarisUkuranDinamis($produk, $ukuranBaris);
                } else {
                    // Jaga-jaga (mis. dipanggil lewat API tanpa JS) — fallback
                    // ke perilaku lama biar produk tetap dapat baris ukuran.
                    $this->pastikanBarisUkuranBesarKecil($produk);
                }
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
            $produk->load(['kategori', 'produkUkurans.ukuran']);

            return view('pages.produk.show', compact('produk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Produk $produk)
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();
            $daftarUkuran = Ukuran::aktif()->urut()->get();
            $produk->load('produkUkurans.ukuran');

            if (old('ukuran_baris') !== null) {
                // Balik dari validasi gagal — pakai draft yang barusan
                // disubmit, bukan data lama dari DB.
                $ukuranBaris = collect(old('ukuran_baris', []))
                    ->map(function ($b) use ($daftarUkuran) {
                        $master = $daftarUkuran->firstWhere(
                            'id',
                            (int) ($b['ukuran_id'] ?? 0)
                        );

                        return [
                            'ukuran_id' => $b['ukuran_id'] ?? '',
                            'nama' => $master->nama_ukuran ?? '',
                            'harga_modal' => $b['harga_modal'] ?? '',
                            'harga_jual' => $b['harga_jual'] ?? '',
                            'stok' => $b['stok'] ?? '',
                            'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                        ];
                    })
                    ->values();
            } else {
                // Prefill dari baris ukuran yang udah tersimpan buat produk ini.
                $ukuranBaris = $produk->produkUkurans
                    ->map(fn ($pu) => [
                        'ukuran_id' => $pu->ukuran_id,
                        'nama' => $pu->ukuran->nama_ukuran ?? '',
                        'harga_modal' => (int) $pu->harga_modal,
                        'harga_jual' => (int) $pu->harga_jual,
                        'stok' => (int) $pu->stok,
                        'status_aktif' => (bool) $pu->status_aktif,
                    ])
                    ->values();
            }

            return view('pages.produk.edit', compact(
                'produk',
                'kategori',
                'daftarUkuran',
                'ukuranBaris'
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
                'harga_modal' => 'nullable|required_if:punya_ukuran,0|numeric|min:0',
                'stok' => 'nullable|required_if:punya_ukuran,0|integer|min:0',
                'stok_minimum' => 'required|integer|min:0',
                'status_aktif' => 'required|boolean',
                'status_ketersediaan' => [
                    'required',
                    Rule::in(['stok_ada', 'perlu_diproduksi']),
                ],
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

                // Sama persis kaya validasi di store(): satu baris = satu
                // ukuran dari master Ukuran, harga modal & jual per-baris.
                'ukuran_baris' => 'nullable|array|required_if:punya_ukuran,1',
                'ukuran_baris.*.ukuran_id' => 'required_with:ukuran_baris|exists:ukurans,id|distinct',
                'ukuran_baris.*.harga_modal' => 'required_with:ukuran_baris|numeric|min:0',
                'ukuran_baris.*.harga_jual' => 'required_with:ukuran_baris|numeric|min:0',
                'ukuran_baris.*.stok' => 'required_with:ukuran_baris|integer|min:0',
            ], [
                'ukuran_baris.required_if' => 'Tambahkan minimal 1 baris ukuran.',
                'ukuran_baris.*.ukuran_id.distinct' => 'Satu ukuran cuma boleh ditambahkan sekali.',
            ]);

            $ukuranBaris = collect($request->input('ukuran_baris', []))
                ->filter(fn ($baris) => !empty($baris['ukuran_id']))
                ->values();

            if ($request->hasFile('foto')) {

                if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->delete($produk->foto);
                }

                $validated['foto'] = $request
                    ->file('foto')
                    ->store(self::FOTO_PATH, 'public');
            }

            $validated['punya_ukuran'] = $request->has('punya_ukuran') ? (bool) $request->punya_ukuran : false;
            if ($validated['punya_ukuran']) {
                $validated['harga_jual'] = 0;
                $validated['harga_modal'] = 0;
                $validated['stok'] = (int) $ukuranBaris->sum(fn ($b) => (int) ($b['stok'] ?? 0));
            }

            unset($validated['ukuran_baris']);

            $validated['status_ketersediaan'] = $validated['stok'] <= 0
                ? 'perlu_diproduksi'
                : 'stok_ada';

            $produk->update($validated);

            if ($validated['punya_ukuran'] && $ukuranBaris->isNotEmpty()) {
                $this->syncBarisUkuranDinamis($produk, $ukuranBaris);
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
            if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
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

            return redirect()
                ->route('produk.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}