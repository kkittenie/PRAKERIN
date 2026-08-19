<?php

namespace App\Http\Controllers;

use App\Models\PengaturanPajak;
use App\Support\ActivityLogHelper;
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
                ->get();

            return view('pages.pengaturan-pajak.index', compact('pajak', 'search'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            return view('pages.pengaturan-pajak.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit($id)
    {
        try {
            $pajak = PengaturanPajak::findOrFail($id);
            return view('pages.pengaturan-pajak.edit', compact('pajak'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
