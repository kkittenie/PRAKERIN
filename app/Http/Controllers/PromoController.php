<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromoController extends Controller
{
    public function indeks(Request $request)
    {
        try {
            $search = $request->search;

            $promo = Promo::withCount('produks')
                ->when($search, function ($query) use ($search) {
                    $query->where('nama_promo', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10);

            return view('pages.promo.index', compact('promo', 'search'));
        } catch (\Exception $e) {
            Log::error('Error pada indeks PromoController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data promo.');
        }
    }

    public function tambah()
    {
        try {
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();
            return view('pages.promo.create', compact('produks'));
        } catch (\Exception $e) {
            Log::error('Error pada tambah PromoController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat halaman tambah promo.');
        }
    }

    public function simpan(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_promo' => 'required|string|max:255',
                'persentase' => 'required|numeric|min:0|max:100',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'required|date|after_or_equal:mulai_berlaku',
                'produk_ids' => 'required|array',
                'produk_ids.*' => 'exists:produks,id',
            ]);

            $promo = Promo::create([
                'nama_promo' => $validated['nama_promo'],
                'persentase' => $validated['persentase'],
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'],
            ]);

            $promo->produks()->sync($validated['produk_ids']);

            return redirect()
                ->route('promo.index')
                ->with('success', 'Promo berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error pada simpan PromoController: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan promo.');
        }
    }

    public function ubah($id)
    {
        try {
            $promo = Promo::with('produks')->findOrFail($id);
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();
            $selectedProduks = $promo->produks->pluck('id')->toArray();

            return view('pages.promo.edit', compact('promo', 'produks', 'selectedProduks'));
        } catch (\Exception $e) {
            Log::error('Error pada ubah PromoController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat halaman edit promo.');
        }
    }

    public function perbarui(Request $request, $id)
    {
        try {
            $promo = Promo::findOrFail($id);

            $validated = $request->validate([
                'nama_promo' => 'required|string|max:255',
                'persentase' => 'required|numeric|min:0|max:100',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'required|date|after_or_equal:mulai_berlaku',
                'produk_ids' => 'required|array',
                'produk_ids.*' => 'exists:produks,id',
            ]);

            $promo->update([
                'nama_promo' => $validated['nama_promo'],
                'persentase' => $validated['persentase'],
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'],
            ]);

            $promo->produks()->sync($validated['produk_ids']);

            return redirect()
                ->route('promo.index')
                ->with('success', 'Promo berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error pada perbarui PromoController: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui promo.');
        }
    }

    public function hapus($id)
    {
        try {
            $promo = Promo::findOrFail($id);
            $promo->produks()->detach();
            $promo->delete();

            return redirect()
                ->route('promo.index')
                ->with('success', 'Promo berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error pada hapus PromoController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus promo.');
        }
    }
}
