<?php

namespace App\Http\Controllers;

use App\Models\DetailProduk;
use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukKhususBundlingController extends Controller
{
    private const FOTO_PATH = 'produk/images';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Produk::with(['kategori', 'detailProduks'])->khususBundling();

                // Kode, stok, dan ketersediaan sekarang ada di detail produk, jadi dicari dan diurutkan dari sana.
                $detailPertama = fn (string $kolom) => DetailProduk::select($kolom)
                    ->whereColumn('detail_produks.produk_id', 'produks.id')
                    ->orderBy('urutan')->orderBy('id')->limit(1);

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->filterColumn('kode_produk', function ($q, $keyword) {
                        $q->whereHas('detailProduks', fn ($qd) => $qd->where('kode_produk', 'like', "%{$keyword}%"));
                    })
                    ->orderColumn('kode_produk', fn ($q, $order) => $q->orderBy($detailPertama('kode_produk'), $order))
                    ->orderColumn('stok', function ($q, $order) {
                        $q->orderBy(
                            DetailProduk::selectRaw('COALESCE(SUM(stok), 0)')->whereColumn('detail_produks.produk_id', 'produks.id'),
                            $order
                        );
                    })
                    ->orderColumn('status_ketersediaan', function ($q, $order) {
                        $q->orderBy(
                            DetailProduk::selectRaw('COALESCE(SUM(CASE WHEN status_aktif = 1 AND stok > 0 THEN 1 ELSE 0 END), 0)')
                                ->whereColumn('detail_produks.produk_id', 'produks.id'),
                            $order
                        );
                    })
                    ->addColumn('foto', fn($item) => view('pages.produk-khusus-bundling.partials.foto', compact('item'))->render())
                    ->addColumn('nama_produk_html', fn($item) => view('pages.produk-khusus-bundling.partials.nama', compact('item'))->render())
                    ->editColumn('kode_produk', fn($item) => view('pages.produk-khusus-bundling.partials.kode', compact('item'))->render())
                    ->addColumn('stok_html', fn($item) => view('pages.produk-khusus-bundling.partials.stok', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.produk-khusus-bundling.partials.status', compact('item'))->render())
                    ->addColumn('ketersediaan_html', fn($item) => view('pages.produk-khusus-bundling.partials.ketersediaan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.produk-khusus-bundling.partials.aksi', compact('item'))->render())
                    ->rawColumns(['foto', 'nama_produk_html', 'kode_produk', 'stok_html', 'status_html', 'ketersediaan_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data produk khusus bundling.'], 500);
            }
        }

        return view('pages.produk-khusus-bundling.index');
    }

    public function toggleStatus(Produk $produk)
    {
        try {
            DB::transaction(function () use ($produk) {
                $produk->update(['status_aktif' => !$produk->status_aktif]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $produk->kode_produk,
                        'nama_produk' => $produk->nama_produk,
                        'status_aktif' => $produk->status_aktif,
                    ])
                    ->log('mengubah status produk khusus bundling');
            });

            return redirect()->back()->with('success', 'Status produk khusus bundling berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();

            return view('pages.produk-khusus-bundling.create', compact('kategori'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->bersihkanAngka($request);

            $validated = $request->validate([
                'kategori_id' => 'required|exists:kategori_produks,id',
                'kode_produk' => 'required|max:50|unique:detail_produks,kode_produk',
                'nama_produk' => 'required|max:100',
                'deskripsi' => 'nullable|string|max:1000',
                'harga_jual' => 'nullable|numeric|min:0',
                'harga_modal' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'stok_minimum' => 'required|integer|min:0',
                'status_aktif' => 'required|boolean',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            DB::transaction(function () use ($request, $validated) {
                $dataProduk = [
                    'kategori_id' => $validated['kategori_id'],
                    'nama_produk' => $validated['nama_produk'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'status_aktif' => $validated['status_aktif'],
                    'is_khusus_bundling' => true,
                ];

                if ($request->hasFile('foto')) {
                    $dataProduk['foto'] = $request->file('foto')->store(self::FOTO_PATH, 'public');
                }

                $produk = Produk::create($dataProduk);

                // Produk khusus bundling juga punya satu detail: kode, harga, dan stoknya ada di sana.
                DetailProduk::create([
                    'produk_id' => $produk->id,
                    'kode_produk' => $validated['kode_produk'],
                    'harga_modal' => $validated['harga_modal'],
                    'harga_jual' => $validated['harga_jual'] ?? 0,
                    'stok' => $validated['stok'],
                    'stok_minimum' => $validated['stok_minimum'],
                    'status_aktif' => true,
                    'urutan' => 0,
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $validated['kode_produk'],
                        'nama_produk' => $validated['nama_produk'],
                        'kategori_id' => $validated['kategori_id'],
                        'stok' => $validated['stok'],
                        'is_khusus_bundling' => true,
                    ])
                    ->log('menambahkan produk khusus bundling');
            });

            return redirect()
                ->route('produk-khusus-bundling.index')
                ->with('success', 'Produk Khusus Bundling berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(Produk $produk)
    {
        try {
            abort_unless($produk->is_khusus_bundling, 404);
            $produk->load(['kategori', 'detailProduks']);

            return view('pages.produk-khusus-bundling.show', compact('produk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Produk $produk)
    {
        try {
            abort_unless($produk->is_khusus_bundling, 404);
            $produk->load('detailProduks');
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();

            return view('pages.produk-khusus-bundling.edit', compact('produk', 'kategori'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Produk $produk)
    {
        try {
            abort_unless($produk->is_khusus_bundling, 404);

            $this->bersihkanAngka($request);

            $detail = $produk->detailProduks()->first();

            $validated = $request->validate([
                'kategori_id' => 'required|exists:kategori_produks,id',
                'kode_produk' => [
                    'required',
                    'max:50',
                    Rule::unique('detail_produks', 'kode_produk')->ignore($detail?->id),
                ],
                'nama_produk' => 'required|max:100',
                'deskripsi' => 'nullable|string|max:1000',
                'harga_jual' => 'nullable|numeric|min:0',
                'harga_modal' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'stok_minimum' => 'required|integer|min:0',
                'status_aktif' => 'required|boolean',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            DB::transaction(function () use ($request, $produk, $detail, $validated) {
                $dataProduk = [
                    'kategori_id' => $validated['kategori_id'],
                    'nama_produk' => $validated['nama_produk'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'status_aktif' => $validated['status_aktif'],
                    'is_khusus_bundling' => true,
                ];

                if ($request->hasFile('foto')) {
                    if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                        Storage::disk('public')->delete($produk->foto);
                    }

                    $dataProduk['foto'] = $request->file('foto')->store(self::FOTO_PATH, 'public');
                }

                $produk->update($dataProduk);

                $dataDetail = [
                    'kode_produk' => $validated['kode_produk'],
                    'harga_modal' => $validated['harga_modal'],
                    'harga_jual' => $validated['harga_jual'] ?? 0,
                    'stok' => $validated['stok'],
                    'stok_minimum' => $validated['stok_minimum'],
                ];

                if ($detail) {
                    $detail->update($dataDetail);
                } else {
                    DetailProduk::create($dataDetail + ['produk_id' => $produk->id, 'status_aktif' => true, 'urutan' => 0]);
                }

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $validated['kode_produk'],
                        'nama_produk' => $validated['nama_produk'],
                        'kategori_id' => $validated['kategori_id'],
                        'stok' => $validated['stok'],
                        'is_khusus_bundling' => true,
                    ])
                    ->log('memperbarui produk khusus bundling');
            });

            return redirect()
                ->route('produk-khusus-bundling.index')
                ->with('success', 'Produk Khusus Bundling berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Produk $produk)
    {
        try {
            abort_unless($produk->is_khusus_bundling, 404);

            // Produk yang sudah pernah dicatat di Riwayat Produksi gak boleh
            // dihapus (FK produksis.produk_id di-restrict, sengaja biar
            // histori produksi lama gak ikut ilang/rusak). Cek dulu di sini
            // biar errornya jelas buat user, bukan raw SQL error.
            if ($produk->produksi()->exists()) {
                return back()->with(
                    'error',
                    "Produk \"{$produk->nama_produk}\" tidak bisa dihapus karena sudah punya riwayat produksi. Nonaktifkan produk ini saja kalau tidak mau dipakai lagi."
                );
            }

            DB::transaction(function () use ($produk) {
                if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->delete($produk->foto);
                }

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $produk->kode_produk,
                        'nama_produk' => $produk->nama_produk,
                        'kategori_id' => $produk->kategori_id,
                        'stok' => $produk->stok,
                        'is_khusus_bundling' => true,
                    ])
                    ->log('menghapus produk khusus bundling');

                $produk->delete();
            });

            return redirect()
                ->route('produk-khusus-bundling.index')
                ->with('success', 'Produk Khusus Bundling berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** Harga di form memakai titik ribuan ("10.000"); ubah jadi angka sebelum divalidasi. */
    private function bersihkanAngka(Request $request): void
    {
        foreach (['harga_modal', 'harga_jual'] as $kolom) {
            if ($request->has($kolom)) {
                $request->merge([$kolom => (float) str_replace('.', '', (string) $request->input($kolom))]);
            }
        }
    }
}