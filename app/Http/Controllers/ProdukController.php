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
     * Folder tempat foto produk disimpan di dalam disk "public".
     * Akan menghasilkan path fisik: storage/app/public/produk/images/...
     * Dan bisa diakses lewat URL: /storage/produk/images/...
     * (pastikan sudah jalankan: php artisan storage:link)
     */
    private const FOTO_PATH = 'produk/images';

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

    public function create()
    {
        $kategori = KategoriProduk::orderBy('nama_kategori')->get();

        return view('pages.produk.create', compact('kategori'));
    }

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
                Rule::in(['stok_ada', 'perlu_diproduksi']),
            ],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Stok yang habis (0) otomatis diset "Perlu Diproduksi", bukan pre-order.
        $validated['status_ketersediaan'] = $validated['stok'] <= 0
            ? 'perlu_diproduksi'
            : 'stok_ada';

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
                'harga_jual' => $validated['harga_jual'],
                'stok' => $validated['stok'],
            ])
            ->log('menambahkan produk');

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Produk $produk)
    {
        //
    }

    public function edit(Produk $produk)
    {
        $kategori = KategoriProduk::orderBy('nama_kategori')->get();

        return view('pages.produk.edit', compact('produk', 'kategori'));
    }

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
                Rule::in(['stok_ada', 'perlu_diproduksi']),
            ],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $validated['status_ketersediaan'] = $validated['stok'] <= 0
            ? 'perlu_diproduksi'
            : 'stok_ada';

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
                'harga_jual' => $validated['harga_jual'],
                'stok' => $validated['stok'],
            ])
            ->log('memperbarui produk');

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
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
    }
}