<?php

namespace App\Http\Controllers;

use App\Models\Bundling;
use App\Models\BundlingItem;
use App\Models\Produk;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BundlingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                // jumlah_produk & perlu_diproduksi adalah accessor (bukan kolom
                // database), jadi sort-nya gak bisa dilakuin lewat ->orderBy()
                // di query builder (bakal error "Unknown column" & bikin
                // DataTables loading terus). Makanya di sini datanya diambil
                // sebagai Collection dulu, biar DataTables sort langsung di
                // attribute yang udah ke-append (lihat Bundling::$appends).
                $data = Bundling::with('items.produk')->get();

                return \Yajra\DataTables\Facades\DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('gambar_html', fn($item) => view('pages.bundling.partials.gambar', compact('item'))->render())
                    ->addColumn('nama_bundling_html', fn($item) => view('pages.bundling.partials.nama', compact('item'))->render())
                    ->editColumn('kode_bundling', fn($item) => view('pages.bundling.partials.kode', compact('item'))->render())
                    ->addColumn('harga_html', fn($item) => view('pages.bundling.partials.harga', compact('item'))->render())
                    ->addColumn('jumlah_produk_html', fn($item) => view('pages.bundling.partials.jumlah_produk', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.bundling.partials.status', compact('item'))->render())
                    ->addColumn('ketersediaan_html', fn($item) => view('pages.bundling.partials.ketersediaan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.bundling.partials.aksi', compact('item'))->render())
                    ->rawColumns(['gambar_html', 'nama_bundling_html', 'kode_bundling', 'harga_html', 'jumlah_produk_html', 'status_html', 'ketersediaan_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data bundling.'], 500);
            }
        }

        return view('pages.bundling.index');
    }

    public function create()
    {
        try {
            $produkBiasa = Produk::reguler()->orderBy('nama_produk')->get();
            $produkKhusus = Produk::khususBundling()->orderBy('nama_produk')->get();
            $produk = Produk::orderBy('nama_produk')->get();

            return view('pages.bundling.create', compact('produk', 'produkBiasa', 'produkKhusus'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(Bundling $bundling)
    {
        try {
            $bundling->status_aktif = !$bundling->status_aktif;
            $bundling->save();

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Status bundling berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $hargaBersih = (int) str_replace('.', '', $request->harga);
            $request->merge(['harga' => $hargaBersih]);

            $request->validate([
                'nama_bundling' => 'required|string|max:150',
                'harga' => 'required|integer|min:1',
                'deskripsi' => 'nullable|string',
                'status_aktif' => 'nullable|boolean',
                'jenis_produk' => 'required|in:biasa,khusus_bundling',
                'total_qty_khusus' => 'required_if:jenis_produk,khusus_bundling|nullable|integer|min:1',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => ['required', 'distinct', 'exists:produks,id'],
                'qty' => 'nullable|array',
                'qty.*' => 'nullable|integer|min:1',
            ]);

            DB::transaction(function () use ($request, &$bundling) {

                $pathGambar = null;
                if ($request->hasFile('gambar')) {
                    $pathGambar = $request->file('gambar')->store('bundling', 'public');
                }

                $bundling = Bundling::create([
                    'nama_bundling' => $request->nama_bundling,
                    'harga' => $request->harga,
                    'deskripsi' => $request->deskripsi,
                    'status_aktif' => $request->boolean('status_aktif', true),
                    'jenis_produk' => $request->jenis_produk,
                    'total_qty_khusus' => $request->jenis_produk === 'khusus_bundling' ? (int) $request->total_qty_khusus : null,
                    'gambar' => $pathGambar,
                ]);

                $bundling->update([
                    'kode_bundling' => 'BDL' . str_pad($bundling->id, 4, '0', STR_PAD_LEFT),
                ]);

                foreach ($request->produk_id as $i => $produkId) {
                    BundlingItem::create([
                        'bundling_id' => $bundling->id,
                        'produk_id' => $produkId,
                        'qty' => isset($request->qty[$i]) ? (int) $request->qty[$i] : 1,
                    ]);
                }
            });

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Bundling berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(Bundling $bundling)
    {
        try {
            $bundling->load('items.produk');

            return view('pages.bundling.show', compact('bundling'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Bundling $bundling)
    {
        try {
            $bundling->load('items.produk');
            $produkBiasa = Produk::reguler()->orderBy('nama_produk')->get();
            $produkKhusus = Produk::khususBundling()->orderBy('nama_produk')->get();
            $produk = Produk::orderBy('nama_produk')->get();

            return view('pages.bundling.edit', compact('bundling', 'produk', 'produkBiasa', 'produkKhusus'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Bundling $bundling)
    {
        try {
            $hargaBersih = (int) str_replace('.', '', $request->harga);
            $request->merge(['harga' => $hargaBersih]);

            $request->validate([
                'nama_bundling' => 'required|string|max:150',
                'harga' => 'required|integer|min:1',
                'deskripsi' => 'nullable|string',
                'status_aktif' => 'nullable|boolean',
                'jenis_produk' => 'required|in:biasa,khusus_bundling',
                'total_qty_khusus' => 'required_if:jenis_produk,khusus_bundling|nullable|integer|min:1',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => ['required', 'distinct', 'exists:produks,id'],
                'qty' => 'nullable|array',
                'qty.*' => 'nullable|integer|min:1',
            ]);

            DB::transaction(function () use ($request, $bundling) {

                $dataUpdate = [
                    'nama_bundling' => $request->nama_bundling,
                    'harga' => $request->harga,
                    'deskripsi' => $request->deskripsi,
                    'jenis_produk' => $request->jenis_produk,
                    'total_qty_khusus' => $request->jenis_produk === 'khusus_bundling' ? (int) $request->total_qty_khusus : null,
                    'status_aktif' => $request->boolean('status_aktif', true),
                ];

                if ($request->hasFile('gambar')) {
                    if ($bundling->gambar && Storage::disk('public')->exists($bundling->gambar)) {
                        Storage::disk('public')->delete($bundling->gambar);
                    }
                    $dataUpdate['gambar'] = $request->file('gambar')->store('bundling', 'public');
                }

                $bundling->update($dataUpdate);

                $bundling->items()->delete();

                foreach ($request->produk_id as $i => $produkId) {
                    BundlingItem::create([
                        'bundling_id' => $bundling->id,
                        'produk_id' => $produkId,
                        'qty' => isset($request->qty[$i]) ? (int) $request->qty[$i] : 1,
                    ]);
                }
            });

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Bundling berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Bundling $bundling)
    {
        try {
            if ($bundling->gambar && Storage::disk('public')->exists($bundling->gambar)) {
                Storage::disk('public')->delete($bundling->gambar);
            }

            $bundling->delete();

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Bundling berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}