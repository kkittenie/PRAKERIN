<?php

namespace App\Http\Controllers;

use App\Models\VarianRasa;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VarianRasaController extends Controller
{
    private const GAMBAR_PATH = 'varian-rasa';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = VarianRasa::query();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('gambar_html', fn ($item) => view('pages.varian-rasa.partials.gambar', compact('item'))->render())
                    ->editColumn('nama_rasa', fn ($item) => '<span class="font-semibold text-gray-900 dark:text-white">' . e($item->nama_rasa) . '</span>')
                    ->addColumn('status_html', fn ($item) => view('pages.varian-rasa.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('pages.varian-rasa.partials.aksi', compact('item'))->render())
                    ->rawColumns(['gambar_html', 'nama_rasa', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data varian rasa.'], 500);
            }
        }

        return view('pages.varian-rasa.index');
    }

    public function create()
    {
        try {
            return view('pages.varian-rasa.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_rasa' => 'required|string|max:100|unique:varian_rasas,nama_rasa',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'status_aktif' => 'required|boolean',
            ]);

            $pathGambar = null;
            if ($request->hasFile('gambar')) {
                $pathGambar = $request->file('gambar')->store(self::GAMBAR_PATH, 'public');
            }

            $varian = VarianRasa::create([
                'nama_rasa' => trim($validated['nama_rasa']),
                'gambar' => $pathGambar,
                'status_aktif' => (bool) $validated['status_aktif'],
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($varian)
                ->withProperties(['nama_rasa' => $varian->nama_rasa])
                ->log('menambahkan varian rasa');

            return redirect()
                ->route('varian-rasa.index')
                ->with('success', 'Varian rasa berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(VarianRasa $varianRasa)
    {
        return redirect()->route('varian-rasa.index');
    }

    public function edit(VarianRasa $varianRasa)
    {
        try {
            return view('pages.varian-rasa.edit', compact('varianRasa'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, VarianRasa $varianRasa)
    {
        try {
            $validated = $request->validate([
                'nama_rasa' => 'required|string|max:100|unique:varian_rasas,nama_rasa,' . $varianRasa->id,
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'status_aktif' => 'required|boolean',
            ]);

            $data = [
                'nama_rasa' => trim($validated['nama_rasa']),
                'status_aktif' => (bool) $validated['status_aktif'],
            ];

            if ($request->hasFile('gambar')) {
                if ($varianRasa->gambar) {
                    Storage::disk('public')->delete($varianRasa->gambar);
                }
                $data['gambar'] = $request->file('gambar')->store(self::GAMBAR_PATH, 'public');
            }

            $varianRasa->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($varianRasa)
                ->withProperties(['nama_rasa' => $varianRasa->nama_rasa])
                ->log('memperbarui varian rasa');

            return redirect()
                ->route('varian-rasa.index')
                ->with('success', 'Varian rasa berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $varian = VarianRasa::findOrFail($id);
            $varian->status_aktif = !$varian->status_aktif;
            $varian->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($varian)
                ->withProperties([
                    'nama_rasa' => $varian->nama_rasa,
                    'status_aktif' => $varian->status_aktif,
                ])
                ->log('mengubah status varian rasa');

            return redirect()
                ->route('varian-rasa.index')
                ->with('success', 'Status "' . $varian->nama_rasa . '"' . ($varian->status_aktif ? ' berhasil diaktifkan.' : ' berhasil dinonaktifkan.'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(VarianRasa $varianRasa)
    {
        try {
            if ($varianRasa->gambar) {
                Storage::disk('public')->delete($varianRasa->gambar);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($varianRasa)
                ->withProperties(['nama_rasa' => $varianRasa->nama_rasa])
                ->log('menghapus varian rasa');

            $varianRasa->delete();

            return redirect()
                ->route('varian-rasa.index')
                ->with('success', 'Varian rasa berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}