<?php

namespace App\Http\Controllers;

use App\Models\PengaturanPajak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengaturanPajakController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->search;

            $pajak = PengaturanPajak::when($search, function ($query) use ($search) {
                    $query->where('nama_pajak', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10);

            return view('pages.pengaturan-pajak.index', compact('pajak', 'search'));
        } catch (\Exception $e) {
            Log::error('Error pada index PengaturanPajakController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pengaturan pajak.');
        }
    }

    public function create()
    {
        try {
            return view('pages.pengaturan-pajak.create');
        } catch (\Exception $e) {
            Log::error('Error pada create PengaturanPajakController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat halaman tambah pajak.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_pajak' => 'required|string|max:255',
                'persentase' => 'required|numeric|min:0|max:100',
                'status_aktif' => 'nullable|boolean',
                'catatan' => 'nullable|string|max:500',
            ]);

            $statusAktif = $request->has('status_aktif') ? (bool)$request->status_aktif : true;

            if ($statusAktif) {
                PengaturanPajak::query()->update(['status_aktif' => false]);
            }

            $pajak = PengaturanPajak::create([
                'nama_pajak' => $validated['nama_pajak'],
                'persentase' => $validated['persentase'],
                'status_aktif' => $statusAktif,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pajak)
                ->withProperties([
                    'nama_pajak' => $pajak->nama_pajak,
                    'persentase' => $pajak->persentase,
                    'status_aktif' => $pajak->status_aktif,
                ])
                ->log('menambahkan pengaturan pajak');

            return redirect()
                ->route('pengaturan-pajak.index')
                ->with('success', 'Pengaturan pajak berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error pada store PengaturanPajakController: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan pengaturan pajak.');
        }
    }

    public function edit($id)
    {
        try {
            $pajak = PengaturanPajak::findOrFail($id);
            return view('pages.pengaturan-pajak.edit', compact('pajak'));
        } catch (\Exception $e) {
            Log::error('Error pada edit PengaturanPajakController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat halaman edit pajak.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pajak = PengaturanPajak::findOrFail($id);

            $validated = $request->validate([
                'nama_pajak' => 'required|string|max:255',
                'persentase' => 'required|numeric|min:0|max:100',
                'status_aktif' => 'nullable|boolean',
                'catatan' => 'nullable|string|max:500',
            ]);

            $statusAktif = $request->has('status_aktif') ? (bool)$request->status_aktif : false;

            if ($statusAktif) {
                PengaturanPajak::where('id', '!=', $pajak->id)->update(['status_aktif' => false]);
            }

            $pajak->update([
                'nama_pajak' => $validated['nama_pajak'],
                'persentase' => $validated['persentase'],
                'status_aktif' => $statusAktif,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pajak)
                ->withProperties([
                    'nama_pajak' => $pajak->nama_pajak,
                    'persentase' => $pajak->persentase,
                    'status_aktif' => $pajak->status_aktif,
                ])
                ->log('memperbarui pengaturan pajak');

            return redirect()
                ->route('pengaturan-pajak.index')
                ->with('success', 'Pengaturan pajak berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error pada update PengaturanPajakController: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui pengaturan pajak.');
        }
    }

    public function toggleStatus($id)
    {
        try {
            $pajak = PengaturanPajak::findOrFail($id);
            $newStatus = !$pajak->status_aktif;

            if ($newStatus) {
                PengaturanPajak::where('id', '!=', $pajak->id)->update(['status_aktif' => false]);
            }

            $pajak->status_aktif = $newStatus;
            $pajak->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pajak)
                ->withProperties([
                    'nama_pajak' => $pajak->nama_pajak,
                    'status_aktif' => $pajak->status_aktif,
                ])
                ->log('mengubah status pajak');

            return redirect()
                ->route('pengaturan-pajak.index')
                ->with('success', 'Status pajak "' . $pajak->nama_pajak . '"' . ($newStatus ? ' berhasil diaktifkan.' : ' berhasil dinonaktifkan.'));
        } catch (\Exception $e) {
            Log::error('Error pada toggleStatus PengaturanPajakController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengubah status pajak.');
        }
    }

    public function destroy($id)
    {
        try {
            $pajak = PengaturanPajak::findOrFail($id);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pajak)
                ->withProperties([
                    'nama_pajak' => $pajak->nama_pajak,
                    'persentase' => $pajak->persentase,
                ])
                ->log('menghapus pengaturan pajak');

            $pajak->delete();

            return redirect()
                ->route('pengaturan-pajak.index')
                ->with('success', 'Pengaturan pajak berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error pada destroy PengaturanPajakController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pengaturan pajak.');
        }
    }
}
