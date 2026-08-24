<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ProdukUkuran;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdukUkuranController extends Controller
{
    public function store(Request $request, Produk $produk)
    {
        try {
            $validated = $request->validate([
                'ukuran_id' => [
                    'required',
                    'exists:ukurans,id',
                    Rule::unique('produk_ukurans', 'ukuran_id')->where('produk_id', $produk->id),
                ],
                'harga_modal' => 'required|numeric|min:0',
                'harga_jual' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'stok_minimum' => 'required|integer|min:0',
            ], [
                'ukuran_id.unique' => 'Produk ini sudah punya baris untuk ukuran tersebut.',
            ]);

            $validated['produk_id'] = $produk->id;
            $validated['status_ketersediaan'] = $validated['stok'] <= 0 ? 'perlu_diproduksi' : 'stok_ada';
            $validated['status_aktif'] = true;

            $produkUkuran = ProdukUkuran::create($validated);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produkUkuran)
                ->withProperties($validated)
                ->log('menambahkan ukuran ke produk');

            return redirect()
                ->route('produk.edit', $produk->id)
                ->with('success', 'Ukuran berhasil ditambahkan ke produk ini.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, ProdukUkuran $produkUkuran)
    {
        try {
            $validated = $request->validate([
                'harga_modal' => 'required|numeric|min:0',
                'harga_jual' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'stok_minimum' => 'required|integer|min:0',
                'status_ketersediaan' => ['required', Rule::in(['stok_ada', 'perlu_diproduksi'])],
            ]);

            $produkUkuran->update($validated);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produkUkuran)
                ->withProperties($validated)
                ->log('memperbarui ukuran produk');

            return redirect()
                ->route('produk.edit', $produkUkuran->produk_id)
                ->with('success', 'Baris ukuran berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $produkUkuran = ProdukUkuran::findOrFail($id);
            $produkUkuran->status_aktif = !$produkUkuran->status_aktif;
            $produkUkuran->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produkUkuran)
                ->withProperties(['status_aktif' => $produkUkuran->status_aktif])
                ->log('mengubah status ukuran produk');

            return redirect()
                ->route('produk.edit', $produkUkuran->produk_id)
                ->with('success', $produkUkuran->status_aktif ? 'Ukuran diaktifkan.' : 'Ukuran dinonaktifkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(ProdukUkuran $produkUkuran)
    {
        try {
            $produkId = $produkUkuran->produk_id;
            $produkUkuran->delete();

            return redirect()
                ->route('produk.edit', $produkId)
                ->with('success', 'Baris ukuran berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}