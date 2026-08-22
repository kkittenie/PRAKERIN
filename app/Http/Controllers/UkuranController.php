<?php

namespace App\Http\Controllers;

use App\Models\Ukuran;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class UkuranController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->search;

            $ukuran = Ukuran::when($search, function ($query) use ($search) {
                $query->where('nama_ukuran', 'like', "%{$search}%");
            })
                ->urut()
                ->get();

            return view('pages.ukuran.index', compact('ukuran'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            return view('pages.ukuran.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_ukuran' => 'required|string|max:50|unique:ukurans,nama_ukuran',
                'urutan' => 'nullable|integer|min:0',
            ]);

            Ukuran::create([
                'nama_ukuran' => trim($request->nama_ukuran),
                'urutan' => $request->urutan ?? 0,
                'status_aktif' => true,
            ]);

            return redirect()
                ->route('ukuran.index')
                ->with('success', 'Ukuran berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(Ukuran $ukuran)
    {
        try {
            return redirect()->route('ukuran.index');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Ukuran $ukuran)
    {
        try {
            return view('pages.ukuran.edit', compact('ukuran'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Ukuran $ukuran)
    {
        try {
            $request->validate([
                'nama_ukuran' => 'required|string|max:50|unique:ukurans,nama_ukuran,' . $ukuran->id,
                'urutan' => 'nullable|integer|min:0',
            ]);

            $ukuran->update([
                'nama_ukuran' => trim($request->nama_ukuran),
                'urutan' => $request->urutan ?? 0,
            ]);

            return redirect()
                ->route('ukuran.index')
                ->with('success', 'Ukuran berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $ukuran = Ukuran::findOrFail($id);
            $ukuran->status_aktif = !$ukuran->status_aktif;
            $ukuran->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($ukuran)
                ->withProperties([
                    'nama_ukuran' => $ukuran->nama_ukuran,
                    'status_aktif' => $ukuran->status_aktif,
                ])
                ->log('mengubah status ukuran');

            return redirect()
                ->route('ukuran.index')
                ->with('success', 'Status "' . $ukuran->nama_ukuran . '"' . ($ukuran->status_aktif ? ' berhasil diaktifkan.' : ' berhasil dinonaktifkan.'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Ukuran $ukuran)
    {
        try {
            // Catatan: begitu Tahap B (produk_ukurans) dibuat, di sini perlu
            // ditambah pengecekan "masih dipakai produk atau enggak" seperti
            // punya Satuan (cekBahanBakus) sebelum boleh dihapus.
            $ukuran->delete();

            return redirect()
                ->route('ukuran.index')
                ->with('success', 'Ukuran berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}