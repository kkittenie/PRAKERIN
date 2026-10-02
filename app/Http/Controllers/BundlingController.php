<?php

namespace App\Http\Controllers;

use App\Models\Bundling;
use App\Models\BundlingItem;
use App\Models\Produk;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BundlingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $data = Bundling::with(['items.produk.produkUkurans.ukuran', 'items.produk.produkVarianRasas.varianRasa', 'items.produk.produkUkuranRasas'])->get();

                return \Yajra\DataTables\Facades\DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('gambar_html', fn($item) => view('pages.bundling.partials.gambar', compact('item'))->render())
                    ->addColumn('nama_bundling_html', fn($item) => view('pages.bundling.partials.nama', compact('item'))->render())
                    ->editColumn('kode_bundling', fn($item) => view('pages.bundling.partials.kode', compact('item'))->render())
                    ->addColumn('harga_html', fn($item) => view('pages.bundling.partials.harga', compact('item'))->render())
                    ->addColumn('jumlah_produk_html', fn($item) => view('pages.bundling.partials.jumlah_produk', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.bundling.partials.status', compact('item'))->render())
                    ->addColumn('ketersediaan_html', fn($item) => view('pages.bundling.partials.ketersediaan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.bundling.partials.aksi', compact('item'))->render())
                    ->rawColumns(['gambar_html', 'nama_bundling_html', 'kode_bundling', 'harga_html', 'jumlah_produk_html', 'status_html', 'ketersediaan_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data bundling.'], 500);
            }
        }

        return view('pages.bundling.index');
    }

    public function create()
    {
        try {
            $produkBiasa = Produk::reguler()
                ->where('status_aktif', true)
                ->with(['produkUkurans.ukuran', 'produkVarianRasas.varianRasa', 'produkUkuranRasas'])
                ->orderBy('nama_produk')
                ->get();
            $produkKhusus = Produk::khususBundling()->orderBy('nama_produk')->get();

            return view('pages.bundling.create', compact('produkBiasa', 'produkKhusus'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(Bundling $bundling)
    {
        try {
            $bundling->status_aktif = !$bundling->status_aktif;
            $bundling->save();

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Status bundling berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $hargaBersih = (int) str_replace('.', '', $request->harga);
            $request->merge(['harga' => $hargaBersih]);

            $request->validate([
                'nama_bundling' => 'required|string|max:150',
                'harga' => 'required|integer|min:1',
                'deskripsi' => 'nullable|string',
                'status_aktif' => 'nullable|boolean',
                'jenis_produk' => 'required|in:biasa,khusus_bundling',
                'total_qty_khusus' => 'required_if:jenis_produk,khusus_bundling|nullable|integer|min:1',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => ['required', 'exists:produks,id'],
                'ukuran' => 'nullable|array',
                'ukuran.*' => 'nullable|string|max:50',
                'varian_rasa' => 'nullable|array',
                'varian_rasa.*' => 'nullable|string|max:100',
                'qty' => 'nullable|array',
                'qty.*' => 'nullable|integer|min:1',
            ]);

            if ($request->jenis_produk === 'biasa') {
                $this->validasiUkuranBundling($request);
            } else {
                $this->validasiDistinctKhusus($request);
            }

            DB::transaction(function () use ($request, &$bundling) {

                $bundling = Bundling::create([
                    'nama_bundling' => $request->nama_bundling,
                    'harga' => $request->harga,
                    'deskripsi' => $request->deskripsi,
                    'status_aktif' => $request->boolean('status_aktif', true),
                    'jenis_produk' => $request->jenis_produk,
                    'total_qty_khusus' => $request->jenis_produk === 'khusus_bundling' ? (int) $request->total_qty_khusus : null,
                    'gambar' => null,
                ]);

                $kodeBundling = 'BDL' . str_pad((string) $bundling->id, 4, '0', STR_PAD_LEFT);
                $bundling->update(['kode_bundling' => $kodeBundling]);

                if ($request->hasFile('gambar')) {
                    $pathGambar = $request->file('gambar')->store('bundling/' . $kodeBundling, 'public');
                    $bundling->update(['gambar' => $pathGambar]);
                }

                $isBiasa = $request->jenis_produk === 'biasa';

                foreach ($request->produk_id as $i => $produkId) {
                    BundlingItem::create([
                        'bundling_id' => $bundling->id,
                        'produk_id' => $produkId,
                        'ukuran' => $isBiasa && !empty($request->ukuran[$i]) ? $request->ukuran[$i] : null,
                        'varian_rasa' => $isBiasa && !empty($request->varian_rasa[$i]) ? $request->varian_rasa[$i] : null,
                        'qty' => isset($request->qty[$i]) ? (int) $request->qty[$i] : 1,
                    ]);
                }
            });

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Bundling berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function validasiUkuranBundling(Request $request): void
    {
        try {
            $produkIds = $request->produk_id ?? [];
            $ukuranList = $request->ukuran ?? [];

            $rasaList = $request->varian_rasa ?? [];
            $rasaBersih = [];

            $produkTerpakai = Produk::whereIn('id', $produkIds)
                ->with(['produkUkurans.ukuran', 'produkVarianRasas.varianRasa', 'produkUkuranRasas'])
                ->get()
                ->keyBy('id');

            $errors = [];
            $kombinasiSudahAda = [];

            foreach ($produkIds as $i => $produkId) {
                $produk = $produkTerpakai->get($produkId);
                if (!$produk) {
                    continue;
                }

                $ukuranBaris = $ukuranList[$i] ?? null;
                $ukuranBaris = $ukuranBaris !== '' ? $ukuranBaris : null;

                if ($produk->punya_ukuran) {
                    $ukuranValid = collect($produk->daftar_ukuran)->pluck('nama')->map(fn ($n) => strtolower($n));

                    if (!$ukuranBaris) {
                        $errors["ukuran.$i"] = "Pilih ukuran untuk produk \"{$produk->nama_produk}\".";
                    } elseif (!$ukuranValid->contains(strtolower($ukuranBaris))) {
                        $errors["ukuran.$i"] = "Ukuran yang dipilih untuk \"{$produk->nama_produk}\" tidak valid.";
                    }
                } else {
                     $ukuranBaris = null;
                }

                // Rasa wajib dipilih kalau produknya punya varian rasa aktif, karena
                // stok disimpan per rasa. Produk tanpa rasa: nilainya dikosongkan.
                $rasaBaris = $rasaList[$i] ?? null;
                $rasaBaris = $rasaBaris !== '' ? $rasaBaris : null;
                $daftarRasa = $produk->punya_varian_rasa
                    ? collect($produk->daftar_varian_rasa)->pluck('nama')
                    : collect();

                if ($daftarRasa->isNotEmpty()) {
                    $rasaCocok = $rasaBaris
                        ? $daftarRasa->first(fn ($nama) => strcasecmp($nama, $rasaBaris) === 0)
                        : null;

                    if (!$rasaBaris) {
                        $errors["varian_rasa.$i"] = "Pilih varian rasa untuk produk \"{$produk->nama_produk}\".";
                    } elseif (!$rasaCocok) {
                        $errors["varian_rasa.$i"] = "Varian rasa yang dipilih untuk \"{$produk->nama_produk}\" tidak valid.";
                    } else {
                        $rasaBaris = $rasaCocok;
                    }
                } else {
                    $rasaBaris = null;
                }
                $rasaBersih[$i] = $rasaBaris;

                $kunci = $produkId . '|' . strtolower((string) $ukuranBaris) . '|' . strtolower((string) $rasaBaris);
                if (isset($kombinasiSudahAda[$kunci])) {
                    $errors["produk_id.$i"] = "Produk \"{$produk->nama_produk}\""
                        . ($ukuranBaris ? " ukuran \"$ukuranBaris\"" : '')
                        . ($rasaBaris ? " rasa \"$rasaBaris\"" : '')
                        . " sudah dipilih di baris lain.";
                }
                $kombinasiSudahAda[$kunci] = true;
            }

            if (!empty($errors)) {
                throw \Illuminate\Validation\ValidationException::withMessages($errors);
            }

            // Simpan nama rasa yang sudah dibersihkan (huruf sesuai master, kosong
            // untuk produk tanpa rasa) supaya store()/update() tinggal memakainya.
            $request->merge(['varian_rasa' => $rasaBersih]);
        } catch (\Illuminate\Validation\ValidationException $e) {
             throw $e;
        } catch (\Throwable $e) {
             activity('error')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error_message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'input' => $request->except(['password', 'password_confirmation', 'pin', 'token', '_token']),
                ])
                ->log('Error validasi ukuran bundling: ' . $e->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                'produk_id' => 'Gagal memvalidasi ukuran produk: ' . $e->getMessage(),
            ]);
        }
    }

    private function validasiDistinctKhusus(Request $request): void
    {
        try {
            $produkIds = $request->produk_id ?? [];
            $sudahDipilih = [];
            $errors = [];

            foreach ($produkIds as $i => $produkId) {
                if (isset($sudahDipilih[$produkId])) {
                    $errors["produk_id.$i"] = 'Produk ini sudah dipilih di baris lain.';
                }
                $sudahDipilih[$produkId] = true;
            }

            if (!empty($errors)) {
                throw \Illuminate\Validation\ValidationException::withMessages($errors);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            activity('error')
                ->causedBy(auth()->user())
                ->withProperties([
                    'error_message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'input' => $request->except(['password', 'password_confirmation', 'pin', 'token', '_token']),
                ])
                ->log('Error validasi produk khusus bundling: ' . $e->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                'produk_id' => 'Gagal memvalidasi produk: ' . $e->getMessage(),
            ]);
        }
    }

    public function show(Bundling $bundling)
    {
        try {
            $bundling->load(['items.produk.produkUkurans.ukuran', 'items.produk.produkVarianRasas.varianRasa', 'items.produk.produkUkuranRasas']);

            return view('pages.bundling.show', compact('bundling'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Bundling $bundling)
    {
        try {
            $bundling->load(['items.produk.produkUkurans.ukuran', 'items.produk.produkVarianRasas.varianRasa', 'items.produk.produkUkuranRasas']);
            $produkBiasa = Produk::reguler()
                ->where('status_aktif', true)
                ->with(['produkUkurans.ukuran', 'produkVarianRasas.varianRasa', 'produkUkuranRasas'])
                ->orderBy('nama_produk')
                ->get();
            $produkKhusus = Produk::khususBundling()->orderBy('nama_produk')->get();

            return view('pages.bundling.edit', compact('bundling', 'produkBiasa', 'produkKhusus'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Bundling $bundling)
    {
        try {
            $hargaBersih = (int) str_replace('.', '', $request->harga);
            $request->merge(['harga' => $hargaBersih]);

            $request->validate([
                'nama_bundling' => 'required|string|max:150',
                'harga' => 'required|integer|min:1',
                'deskripsi' => 'nullable|string',
                'status_aktif' => 'nullable|boolean',
                'jenis_produk' => 'required|in:biasa,khusus_bundling',
                'total_qty_khusus' => 'required_if:jenis_produk,khusus_bundling|nullable|integer|min:1',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => ['required', 'exists:produks,id'],
                'ukuran' => 'nullable|array',
                'ukuran.*' => 'nullable|string|max:50',
                'varian_rasa' => 'nullable|array',
                'varian_rasa.*' => 'nullable|string|max:100',
                'qty' => 'nullable|array',
                'qty.*' => 'nullable|integer|min:1',
            ]);

            if ($request->jenis_produk === 'biasa') {
                $this->validasiUkuranBundling($request);
            } else {
                $this->validasiDistinctKhusus($request);
            }

            DB::transaction(function () use ($request, $bundling) {

                $isBiasa = $request->jenis_produk === 'biasa';

                $dataUpdate = [
                    'nama_bundling' => $request->nama_bundling,
                    'harga' => $request->harga,
                    'deskripsi' => $request->deskripsi,
                    'jenis_produk' => $request->jenis_produk,
                    'total_qty_khusus' => $isBiasa ? null : (int) $request->total_qty_khusus,
                    'status_aktif' => $request->boolean('status_aktif', true),
                ];

                if ($request->hasFile('gambar')) {
                    $kodeBundling = $bundling->kode_bundling ?: ('BDL' . str_pad((string) $bundling->id, 4, '0', STR_PAD_LEFT));
                    if ($kodeBundling) {
                        Storage::disk('public')->deleteDirectory('bundling/' . $kodeBundling);
                    }
                    $dataUpdate['gambar'] = $request->file('gambar')->store('bundling/' . $kodeBundling, 'public');
                }

                $bundling->update($dataUpdate);

                $bundling->items()->delete();

                foreach ($request->produk_id as $i => $produkId) {
                    BundlingItem::create([
                        'bundling_id' => $bundling->id,
                        'produk_id' => $produkId,
                        'ukuran' => $isBiasa && !empty($request->ukuran[$i]) ? $request->ukuran[$i] : null,
                        'varian_rasa' => $isBiasa && !empty($request->varian_rasa[$i]) ? $request->varian_rasa[$i] : null,
                        'qty' => isset($request->qty[$i]) ? (int) $request->qty[$i] : 1,
                    ]);
                }
            });

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Bundling berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Bundling $bundling)
    {
        try {
            $kodeBundling = $bundling->kode_bundling ?: ('BDL' . str_pad((string) $bundling->id, 4, '0', STR_PAD_LEFT));
            if ($kodeBundling) {
                Storage::disk('public')->deleteDirectory('bundling/' . $kodeBundling);
            } elseif ($bundling->gambar && Storage::disk('public')->exists($bundling->gambar)) {
                Storage::disk('public')->delete($bundling->gambar);
            }

            $bundling->delete();

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Bundling berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}