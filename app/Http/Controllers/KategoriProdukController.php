<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriProduk;

class KategoriProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kategori_produk.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'nama_kategori' => 'required|unique:kategori_produks,nama_kategori|max:100',
        ]);

        KategoriProduk::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()
            ->route('kategori-produk.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriProduk $kategori_produk)
    {
        return view('kategori_produk.edit', compact('kategori_produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KategoriProduk $kategori_produk)
    {
        $request->validate([
        'nama_kategori' => 'required|max:100|unique:kategori_produks,nama_kategori,' . $kategori_produk->id,
        ]);

        $kategori_produk->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()
            ->route('kategori-produk.index')
            ->with('success', 'Kategori berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriProduk $kategori_produk)
    {
            $kategori_produk->delete();

            return redirect()
                ->route('kategori-produk.index')
                ->with('success', 'Kategori berhasil dihapus.');
    }
}
