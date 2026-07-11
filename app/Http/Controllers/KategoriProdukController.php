<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\KategoriProduk;

class KategoriProdukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $kategori = KategoriProduk::withCount('produk')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_kategori', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('pages.kategori-produk.index', compact('kategori', 'search'));
    }

    public function create()
    {
        return view('pages.kategori-produk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_produks,nama_kategori',
        ]);

        KategoriProduk::create($validated);

        return redirect()
            ->route('kategori-produk.index')
            ->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(KategoriProduk $kategori_produk)
    {
        return view('pages.kategori-produk.edit', compact('kategori_produk'));
    }

    public function update(Request $request, KategoriProduk $kategori_produk)
    {
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

        return redirect()
            ->route('kategori-produk.index')
            ->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(KategoriProduk $kategori_produk)
    {
         if ($kategori_produk->produk()->exists()) {

        return redirect()
            ->route('kategori-produk.index')
            ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');

    }

    $kategori_produk->delete();

    return redirect()
        ->route('kategori-produk.index')
        ->with('success', 'Kategori berhasil dihapus.');
    }
}
