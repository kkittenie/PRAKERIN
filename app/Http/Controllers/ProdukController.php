<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\KategoriProduk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $produk = Produk::with('kategori')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_produk', 'like', "%{$search}%")
                      ->orWhere('kode_produk', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('pages.produk.index', compact('produk', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = KategoriProduk::orderBy('nama_kategori')->get();

        return view('pages.produk.create', compact('kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'kategori_id' => 'required|exists:kategori_produks,id',
        'kode_produk' => 'required|max:50|unique:produks,kode_produk',
        'nama_produk' => 'required|max:100',
        'harga_jual' => 'required|numeric|min:0',
        'harga_modal' => 'required|numeric|min:0',
        'stok' => 'required|integer|min:0',
        'stok_minimum' => 'required|integer|min:0',
        'status_aktif' => 'required|boolean',
        'status_ketersediaan' => [
            'required',
            Rule::in(['stok_ada', 'pesan_dulu']),
        ],
        'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('produk', 'public');
        }

        Produk::create($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produk $produk)
    {
        $kategori = KategoriProduk::orderBy('nama_kategori')->get();

        return view('pages.produk.edit', compact('produk', 'kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_produks,id',
            'kode_produk' => [
                'required',
                'max:50',
                Rule::unique('produks', 'kode_produk')->ignore($produk->id),
            ],
            'nama_produk' => 'required|max:100',
            'harga_jual' => 'required|numeric|min:0',
            'harga_modal' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'status_aktif' => 'required|boolean',
            'status_ketersediaan' => [
                'required',
                Rule::in(['stok_ada', 'pesan_dulu']),
            ],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {

            if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                Storage::disk('public')->delete($produk->foto);
            }

            $validated['foto'] = $request
                ->file('foto')
                ->store('produk', 'public');
        }

        $produk->update($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
         if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
        Storage::disk('public')->delete($produk->foto);
        }

        $produk->delete();

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}