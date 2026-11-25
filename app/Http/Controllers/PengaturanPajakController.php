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
        if ($request->ajax()) {
            try {
                $query = PengaturanPajak::query();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('persentase', fn ($item) => rtrim(rtrim(number_format($item->persentase, 2, ',', '.'), '0'), ',') . '%')
                    ->editColumn('catatan', fn ($item) => $item->catatan ?? '-')
                    ->addColumn('status_html', fn ($item) => view('admin.pengaturan-pajak.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('admin.pengaturan-pajak.partials.aksi', compact('item'))->render())
                    ->rawColumns(['status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data pengaturan pajak.'], 500);
            }
        }

        return view('admin.pengaturan-pajak.index');
    }

    public function create()
    {
        try {
            return view('admin.pengaturan-pajak.create');
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
            return view('admin.pengaturan-pajak.edit', compact('pajak'));
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
