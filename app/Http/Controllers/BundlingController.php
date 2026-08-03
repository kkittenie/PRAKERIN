<?php

namespace App\Http\Controllers;

use App\Models\Bundling;
use App\Models\BundlingItem;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BundlingController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $bundling = Bundling::with('items.produk')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_bundling', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('pages.bundling.index', compact('bundling', 'search'));
    }

    public function create()
    {
        $produk = Produk::orderBy('nama_produk')->get();

        return view('pages.bundling.create', compact('produk'));
    }

    public function toggleStatus(Bundling $bundling)
    {
        $bundling->status_aktif = !$bundling->status_aktif;
        $bundling->save();

        return redirect()
            ->route('bundling.index')
            ->with('success', 'Status bundling berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        $hargaBersih = (int) str_replace('.', '', $request->harga);
        $request->merge(['harga' => $hargaBersih]);

        $request->validate([
            'nama_bundling' => 'required|string|max:150',
            'harga' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'status_aktif' => 'nullable|boolean',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => ['required', 'distinct', 'exists:produks,id'],
            'qty' => 'required|array|min:1',
            'qty.*' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, &$bundling) {

            $pathGambar = null;
            if ($request->hasFile('gambar')) {
                $pathGambar = $request->file('gambar')->store('bundling', 'public');
            }

            $bundling = Bundling::create([
                'nama_bundling' => $request->nama_bundling,
                'harga' => $request->harga,
                'deskripsi' => $request->deskripsi,
                'gambar' => $pathGambar,
            ]);

            $bundling->update([
                'kode_bundling' => 'BDL' . str_pad($bundling->id, 4, '0', STR_PAD_LEFT),
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                BundlingItem::create([
                    'bundling_id' => $bundling->id,
                    'produk_id' => $produkId,
                    'qty' => $request->qty[$i],
                ]);
            }
        });

        return redirect()
            ->route('bundling.index')
            ->with('success', 'Bundling berhasil ditambahkan.');
    }

    public function show(Bundling $bundling)
    {
        $bundling->load('items.produk');

        return view('pages.bundling.show', compact('bundling'));
    }

    public function edit(Bundling $bundling)
    {
        $bundling->load('items.produk');
        $produk = Produk::orderBy('nama_produk')->get();

        return view('pages.bundling.edit', compact('bundling', 'produk'));
    }

    public function update(Request $request, Bundling $bundling)
    {
        $hargaBersih = (int) str_replace('.', '', $request->harga);
        $request->merge(['harga' => $hargaBersih]);

        $request->validate([
            'nama_bundling' => 'required|string|max:150',
            'harga' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'status_aktif' => 'nullable|boolean',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => ['required', 'distinct', 'exists:produks,id'],
            'qty' => 'required|array|min:1',
            'qty.*' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $bundling) {

            $dataUpdate = [
                'nama_bundling' => $request->nama_bundling,
                'harga' => $request->harga,
                'deskripsi' => $request->deskripsi,
            ];

            if ($request->hasFile('gambar')) {
                if ($bundling->gambar && Storage::disk('public')->exists($bundling->gambar)) {
                    Storage::disk('public')->delete($bundling->gambar);
                }
                $dataUpdate['gambar'] = $request->file('gambar')->store('bundling', 'public');
            }

            $bundling->update($dataUpdate);

            $bundling->items()->delete();

            foreach ($request->produk_id as $i => $produkId) {
                BundlingItem::create([
                    'bundling_id' => $bundling->id,
                    'produk_id' => $produkId,
                    'qty' => $request->qty[$i],
                ]);
            }
        });

        return redirect()
            ->route('bundling.index')
            ->with('success', 'Bundling berhasil diperbarui.');
    }

    public function destroy(Bundling $bundling)
    {
        if ($bundling->gambar && Storage::disk('public')->exists($bundling->gambar)) {
            Storage::disk('public')->delete($bundling->gambar);
        }

        $bundling->delete();

        return redirect()
            ->route('bundling.index')
            ->with('success', 'Bundling berhasil dihapus.');
    }
}