<?php

namespace App\Http\Controllers;

use App\Models\Ukuran;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class UkuranController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Ukuran::query();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('nama_ukuran', fn ($item) => view('pages.ukuran.partials.nama', compact('item'))->render())
                    ->addColumn('urutan_html', fn ($item) => view('pages.ukuran.partials.urutan', compact('item'))->render())
                    ->addColumn('status_html', fn ($item) => view('pages.ukuran.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('pages.ukuran.partials.aksi', compact('item'))->render())
                    ->rawColumns(['nama_ukuran', 'urutan_html', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data ukuran.'], 500);
            }
        }

        return view('pages.ukuran.index');
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
            $validated = $request->validate([
                'nama_ukuran' => 'required|string|max:50|unique:ukurans,nama_ukuran',
                'urutan' => 'nullable|integer|min:0',
            ]);

            $ukuran = Ukuran::create([
                'nama_ukuran' => trim($validated['nama_ukuran']),
                'urutan' => $validated['urutan'] ?? 0,
                'status_aktif' => true,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($ukuran)
                ->withProperties([
                    'nama_ukuran' => $ukuran->nama_ukuran,
                ])
                ->log('menambahkan ukuran');

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
            $validated = $request->validate([
                'nama_ukuran' => 'required|string|max:50|unique:ukurans,nama_ukuran,' . $ukuran->id,
                'urutan' => 'nullable|integer|min:0',
            ]);

            $ukuran->update([
                'nama_ukuran' => trim($validated['nama_ukuran']),
                'urutan' => $validated['urutan'] ?? 0,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($ukuran)
                ->withProperties([
                    'nama_ukuran' => $ukuran->nama_ukuran,
                ])
                ->log('memperbarui ukuran');

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
            if ($ukuran->produkUkurans()->exists()) {
                return redirect()
                    ->route('ukuran.index')
                    ->with('error', 'Ukuran tidak dapat dihapus karena masih digunakan oleh salah satu produk.');
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($ukuran)
                ->withProperties([
                    'nama_ukuran' => $ukuran->nama_ukuran,
                ])
                ->log('menghapus ukuran');

            $ukuran->delete();

            return redirect()
                ->route('ukuran.index')
                ->with('success', 'Ukuran berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}