<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukKhususBundlingController extends Controller
{
    private const FOTO_PATH = 'produk/images';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Produk::with('kategori')->khususBundling();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addColumn('foto', fn($item) => view('pages.produk-khusus-bundling.partials.foto', compact('item'))->render())
                    ->addColumn('nama_produk_html', fn($item) => view('pages.produk-khusus-bundling.partials.nama', compact('item'))->render())
                    ->editColumn('kode_produk', fn($item) => view('pages.produk-khusus-bundling.partials.kode', compact('item'))->render())
                    ->addColumn('stok_html', fn($item) => view('pages.produk-khusus-bundling.partials.stok', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.produk-khusus-bundling.partials.status', compact('item'))->render())
                    ->addColumn('ketersediaan_html', fn($item) => view('pages.produk-khusus-bundling.partials.ketersediaan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.produk-khusus-bundling.partials.aksi', compact('item'))->render())
                    ->rawColumns(['foto', 'nama_produk_html', 'kode_produk', 'stok_html', 'status_html', 'ketersediaan_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data produk khusus bundling.'], 500);
            }
        }

        return view('pages.produk-khusus-bundling.index');
    }

    public function toggleStatus(Produk $produk)
    {
        try {
            DB::transaction(function () use ($produk) {
                $produk->update(['status_aktif' => !$produk->status_aktif]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $produk->kode_produk,
                        'nama_produk' => $produk->nama_produk,
                        'status_aktif' => $produk->status_aktif,
                    ])
                    ->log('mengubah status produk khusus bundling');
            });

            return redirect()->back()->with('success', 'Status produk khusus bundling berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();

            return view('pages.produk-khusus-bundling.create', compact('kategori'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            if ($request->has('harga_modal')) {
                $request->merge(['harga_modal' => (float) str_replace('.', '', $request->harga_modal)]);
            }
            if ($request->has('harga_jual')) {
                $request->merge(['harga_jual' => (float) str_replace('.', '', $request->harga_jual)]);
            }

            $validated = $request->validate([
                'kategori_id' => 'required|exists:kategori_produks,id',
                'kode_produk' => 'required|max:50|unique:produks,kode_produk',
                'nama_produk' => 'required|max:100',
                'deskripsi' => 'nullable|string|max:1000',
                'harga_jual' => 'nullable|numeric|min:0',
                'harga_modal' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'stok_minimum' => 'required|integer|min:0',
                'status_aktif' => 'required|boolean',
                'status_ketersediaan' => [
                    'required',
                    Rule::in(['stok_ada', 'perlu_diproduksi']),
                ],
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $validated['is_khusus_bundling'] = true;
            $validated['punya_ukuran'] = false;
            $validated['harga_jual'] = $validated['harga_jual'] ?? 0;

            $validated['status_ketersediaan'] = $validated['stok'] <= 0
                ? 'perlu_diproduksi'
                : 'stok_ada';

            DB::transaction(function () use ($request, &$validated) {
                if ($request->hasFile('foto')) {
                    $validated['foto'] = $request->file('foto')->store(self::FOTO_PATH, 'public');
                }

                $produk = Produk::create($validated);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $validated['kode_produk'],
                        'nama_produk' => $validated['nama_produk'],
                        'kategori_id' => $validated['kategori_id'],
                        'stok' => $validated['stok'],
                        'is_khusus_bundling' => true,
                    ])
                    ->log('menambahkan produk khusus bundling');
            });

            return redirect()
                ->route('produk-khusus-bundling.index')
                ->with('success', 'Produk Khusus Bundling berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(Produk $produk)
    {
        try {
            abort_unless($produk->is_khusus_bundling, 404);
            $produk->load('kategori');

            return view('pages.produk-khusus-bundling.show', compact('produk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Produk $produk)
    {
        try {
            abort_unless($produk->is_khusus_bundling, 404);
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();

            return view('pages.produk-khusus-bundling.edit', compact('produk', 'kategori'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Produk $produk)
    {
        try {
            abort_unless($produk->is_khusus_bundling, 404);

            if ($request->has('harga_modal')) {
                $request->merge(['harga_modal' => (float) str_replace('.', '', $request->harga_modal)]);
            }
            if ($request->has('harga_jual')) {
                $request->merge(['harga_jual' => (float) str_replace('.', '', $request->harga_jual)]);
            }

            $validated = $request->validate([
                'kategori_id' => 'required|exists:kategori_produks,id',
                'kode_produk' => [
                    'required',
                    'max:50',
                    Rule::unique('produks', 'kode_produk')->ignore($produk->id),
                ],
                'nama_produk' => 'required|max:100',
                'deskripsi' => 'nullable|string|max:1000',
                'harga_jual' => 'nullable|numeric|min:0',
                'harga_modal' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'stok_minimum' => 'required|integer|min:0',
                'status_aktif' => 'required|boolean',
                'status_ketersediaan' => [
                    'required',
                    Rule::in(['stok_ada', 'perlu_diproduksi']),
                ],
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);


            $validated['is_khusus_bundling'] = true;
            $validated['punya_ukuran'] = false;
            $validated['harga_jual'] = $validated['harga_jual'] ?? 0;

            $validated['status_ketersediaan'] = $validated['stok'] <= 0
                ? 'perlu_diproduksi'
                : 'stok_ada';

            DB::transaction(function () use ($request, $produk, &$validated) {
                if ($request->hasFile('foto')) {
                    if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                        Storage::disk('public')->delete($produk->foto);
                    }

                    $validated['foto'] = $request
                        ->file('foto')
                        ->store(self::FOTO_PATH, 'public');
                }

                $produk->update($validated);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $validated['kode_produk'],
                        'nama_produk' => $validated['nama_produk'],
                        'kategori_id' => $validated['kategori_id'],
                        'stok' => $validated['stok'],
                        'is_khusus_bundling' => true,
                    ])
                    ->log('memperbarui produk khusus bundling');
            });

            return redirect()
                ->route('produk-khusus-bundling.index')
                ->with('success', 'Produk Khusus Bundling berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Produk $produk)
    {
        try {
            abort_unless($produk->is_khusus_bundling, 404);

            DB::transaction(function () use ($produk) {
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
                        'stok' => $produk->stok,
                        'is_khusus_bundling' => true,
                    ])
                    ->log('menghapus produk khusus bundling');

                $produk->delete();
            });

            return redirect()
                ->route('produk-khusus-bundling.index')
                ->with('success', 'Produk Khusus Bundling berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
