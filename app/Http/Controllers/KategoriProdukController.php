<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\KategoriProduk;
use App\Support\ActivityLogHelper;

class KategoriProdukController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->search;

            $kategori = KategoriProduk::withCount('produk')
                ->when($search, function ($query) use ($search) {
                    $query->where('nama_kategori', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10);

            return view('pages.kategori-produk.index', compact('kategori', 'search'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            return view('pages.kategori-produk.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_kategori' => 'required|string|max:100|unique:kategori_produks,nama_kategori',
            ]);

            $kategori = KategoriProduk::create($validated);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kategori)
                ->withProperties([
                    'nama_kategori' => $validated['nama_kategori'],
                ])
                ->log('menambahkan kategori produk');

            return redirect()
                ->route('kategori-produk.index')
                ->with('success', 'Kategori produk berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(KategoriProduk $kategori_produk)
    {
        try {
            return view('pages.kategori-produk.edit', compact('kategori_produk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, KategoriProduk $kategori_produk)
    {
        try {
            $validated = $request->validate([
                'nama_kategori' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('kategori_produks', 'nama_kategori')
                        ->ignore($kategori_produk->id),
                ],
            ]);

            $kategori_produk->update($validated);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kategori_produk)
                ->withProperties([
                    'nama_kategori' => $validated['nama_kategori'],
                ])
                ->log('memperbarui kategori produk');

            return redirect()
                ->route('kategori-produk.index')
                ->with('success', 'Kategori produk berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(KategoriProduk $kategori_produk)
    {
        try {
            if ($kategori_produk->produk()->exists()) {
                return redirect()
                    ->route('kategori-produk.index')
                    ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($kategori_produk)
                ->withProperties([
                    'nama_kategori' => $kategori_produk->nama_kategori,
                ])
                ->log('menghapus kategori produk');

            $kategori_produk->delete();

            return redirect()
                ->route('kategori-produk.index')
                ->with('success', 'Kategori berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
