<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SatuanController extends Controller
{
    /**
     * Daftar alias satuan yang dianggap sama.
     */
    private array $aliases = [
        ['kg', 'kilogram'],
        ['gr', 'gram'],
        ['g'],
        ['ons'],
        ['l', 'liter'],
        ['ml', 'mililiter'],
        ['pcs', 'piece', 'buah'],
        ['pack', 'pak'],
        ['botol'],
        ['dus', 'box'],
    ];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Satuan::query();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('aksi', fn ($item) => view('admin.satuan.partials.aksi', compact('item'))->render())
                    ->rawColumns(['aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data satuan.'], 500);
            }
        }

        return view('admin.satuan.index');
    }

    public function create()
    {
        try {
            return view('admin.satuan.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:50',
            ]);

            $this->cekDuplikatAlias($request->nama);

            Satuan::create([
                'nama' => trim($request->nama),
            ]);

            return redirect()
                ->route('satuan.index')
                ->with('success', 'Satuan berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(Satuan $satuan)
    {
        try {
            return redirect()->route('satuan.index');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Satuan $satuan)
    {
        try {
            return view('admin.satuan.edit', compact('satuan'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Satuan $satuan)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:50',
            ]);

            $this->cekDuplikatAlias($request->nama, $satuan->id);

            $satuan->update([
                'nama' => trim($request->nama),
            ]);

            return redirect()
                ->route('satuan.index')
                ->with('success', 'Satuan berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Satuan $satuan)
    {
        try {
            if ($satuan->bahanBakus()->exists()) {
                return redirect()
                    ->route('satuan.index')
                    ->with('error', 'Satuan tidak dapat dihapus karena masih digunakan pada data bahan baku.');
            }

            $satuan->delete();

            return redirect()
                ->route('satuan.index')
                ->with('success', 'Satuan berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Simpan satuan melalui AJAX.
     */
    public function storeAjax(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:50',
            ]);

            $this->cekDuplikatAlias($request->nama);

            $satuan = Satuan::create([
                'nama' => trim($request->nama),
            ]);

            return response()->json([
                'success' => true,
                'data' => $satuan
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Mengecek apakah satuan atau aliasnya sudah ada.
     */
    private function cekDuplikatAlias(string $nama, ?int $ignoreId = null): void
    {
        $nama = strtolower(trim($nama));

        foreach ($this->aliases as $group) {
            if (in_array($nama, $group)) {
                $query = Satuan::query();

                if ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                }

                foreach ($group as $alias) {
                    if ((clone $query)->whereRaw('LOWER(nama)=?', [$alias])->exists()) {
                        throw ValidationException::withMessages([
                            'nama' => 'Nama satuan sudah ada atau menggunakan istilah yang sama.',
                        ]);
                    }
                }

                return;
            }
        }

        $query = Satuan::whereRaw('LOWER(nama)=?', [$nama]);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nama' => 'Nama satuan sudah ada.',
            ]);
        }
    }
}