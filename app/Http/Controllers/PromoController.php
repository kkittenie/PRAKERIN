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
            // Jaga-jaga: normalisasi koma (format Indonesia) menjadi titik
            // untuk field numerik, seandainya JS di client tidak jalan.
            $this->normalisasiInputKoma($request);

            $validated = $request->validate([
                'nama_promo' => 'required|string|max:255',
                'jenis_potongan' => 'required|in:persentase,nominal',
                'persentase' => 'nullable|required_if:jenis_potongan,persentase|numeric|min:0|max:100',
                'nominal_potongan' => 'nullable|required_if:jenis_potongan,nominal|numeric|min:0',
                'min_pembelian' => 'nullable|numeric|min:0',
                'status_aktif' => 'nullable|boolean',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'nullable|date|after_or_equal:mulai_berlaku',
                'produk_ids' => 'required|array',
                'produk_ids.*' => 'exists:produks,id',
            ]);

            $promo = Promo::create([
                'nama_promo' => $validated['nama_promo'],
                'jenis_potongan' => $validated['jenis_potongan'],
                'persentase' => $validated['jenis_potongan'] === 'persentase' ? ($validated['persentase'] ?? 0) : 0,
                'nominal_potongan' => $validated['jenis_potongan'] === 'nominal' ? ($validated['nominal_potongan'] ?? 0) : 0,
                'min_pembelian' => $validated['min_pembelian'] ?? 0,
                'status_aktif' => $request->has('status_aktif') ? (bool)$request->status_aktif : true,
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'] ?? null,
            ]);

            $promo->produks()->sync($validated['produk_ids']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'jenis_potongan' => $promo->jenis_potongan,
                    'potongan' => $promo->jenis_potongan === 'nominal' ? $promo->nominal_potongan : $promo->persentase,
                    'produks' => $validated['produk_ids'],
                ])
                ->log('menambahkan promo');

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

            // Jaga-jaga: normalisasi koma (format Indonesia) menjadi titik
            // untuk field numerik, seandainya JS di client tidak jalan.
            $this->normalisasiInputKoma($request);

            $validated = $request->validate([
                'nama_promo' => 'required|string|max:255',
                'jenis_potongan' => 'required|in:persentase,nominal',
                'persentase' => 'nullable|required_if:jenis_potongan,persentase|numeric|min:0|max:100',
                'nominal_potongan' => 'nullable|required_if:jenis_potongan,nominal|numeric|min:0',
                'min_pembelian' => 'nullable|numeric|min:0',
                'status_aktif' => 'nullable|boolean',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'nullable|date|after_or_equal:mulai_berlaku',
                'produk_ids' => 'required|array',
                'produk_ids.*' => 'exists:produks,id',
            ]);

            $promo->update([
                'nama_promo' => $validated['nama_promo'],
                'jenis_potongan' => $validated['jenis_potongan'],
                'persentase' => $validated['jenis_potongan'] === 'persentase' ? ($validated['persentase'] ?? 0) : 0,
                'nominal_potongan' => $validated['jenis_potongan'] === 'nominal' ? ($validated['nominal_potongan'] ?? 0) : 0,
                'min_pembelian' => $validated['min_pembelian'] ?? 0,
                'status_aktif' => $request->has('status_aktif') ? (bool)$request->status_aktif : false,
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'] ?? null,
            ]);

            $promo->produks()->sync($validated['produk_ids']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'jenis_potongan' => $promo->jenis_potongan,
                    'potongan' => $promo->jenis_potongan === 'nominal' ? $promo->nominal_potongan : $promo->persentase,
                    'produks' => $validated['produk_ids'],
                ])
                ->log('memperbarui promo');

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

    public function toggleStatus($id)
    {
        try {
            $promo = Promo::findOrFail($id);
            $promo->status_aktif = !$promo->status_aktif;
            $promo->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'status_aktif' => $promo->status_aktif,
                ])
                ->log('mengubah status promo');

            return redirect()
                ->route('promo.index')
                ->with('success', 'Status promo "' . $promo->nama_promo . '"' . ($promo->status_aktif ? ' berhasil diaktifkan.' : ' berhasil dinonaktifkan.'));
        } catch (\Exception $e) {
            Log::error('Error pada toggleStatus PromoController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengubah status promo.');
        }
    }

    public function hapus($id)
    {
        try {
            $promo = Promo::findOrFail($id);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'persentase' => $promo->persentase,
                    'produks' => $promo->produks->pluck('id')->toArray(),
                ])
                ->log('menghapus promo');

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

    /**
     * Ubah pemisah desimal koma (format Indonesia, mis. "15,5") menjadi
     * titik (format standar PHP, mis. "15.5") pada field-field numerik,
     * sebagai fallback jika hidden input dari JS tidak terkirim.
     */
    private function normalisasiInputKoma(Request $request): void
    {
        foreach (['persentase', 'nominal_potongan', 'min_pembelian'] as $field) {
            if ($request->filled($field) && is_string($request->input($field))) {
                $request->merge([
                    $field => str_replace(',', '.', $request->input($field)),
                ]);
            }
        }
    }
}