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
            // Cuma produk reguler yang bisa dimasukkan ke bundling — produk
            // khusus bundling (varian rasa) udah gak dipakai lagi di sini.
            $produkBiasa = Produk::reguler()
                ->where('status_aktif', true)
                ->with('produkUkurans.ukuran')
                ->orderBy('nama_produk')
                ->get();

            return view('pages.bundling.create', compact('produkBiasa'));
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
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => ['required', 'exists:produks,id'],
                'ukuran' => 'nullable|array',
                'ukuran.*' => 'nullable|string|max:50',
                'qty' => 'nullable|array',
                'qty.*' => 'nullable|integer|min:1',
            ]);

            $this->validasiUkuranBundling($request);

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
                    'jenis_produk' => 'biasa',
                    'total_qty_khusus' => null,
                    'gambar' => $pathGambar,
                ]);

                $bundling->update([
                    'kode_bundling' => 'BDL' . str_pad($bundling->id, 4, '0', STR_PAD_LEFT),
                ]);

                foreach ($request->produk_id as $i => $produkId) {
                    BundlingItem::create([
                        'bundling_id' => $bundling->id,
                        'produk_id' => $produkId,
                        'ukuran' => !empty($request->ukuran[$i]) ? $request->ukuran[$i] : null,
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

    /**
     * Validasi manual buat kombinasi produk + ukuran di form bundling:
     * - Produk yang punya varian ukuran (punya_ukuran) WAJIB milih salah satu
     *   ukuran aktifnya.
     * - Produk yang gak punya ukuran gak boleh dikirimin ukuran.
     * - Kombinasi produk+ukuran yang sama gak boleh dipilih dua kali di baris
     *   yang berbeda (tapi produk yang sama dengan ukuran BEDA boleh).
     */
    private function validasiUkuranBundling(Request $request): void
    {
        $produkIds = $request->produk_id ?? [];
        $ukuranList = $request->ukuran ?? [];

        $produkTerpakai = Produk::whereIn('id', $produkIds)
            ->with('produkUkurans.ukuran')
            ->get()
            ->keyBy('id');

        $errors = [];
        $kombinasiSudahAda = [];

        foreach ($produkIds as $i => $produkId) {
            $produk = $produkTerpakai->get($produkId);
            if (!$produk) {
                continue;
            }

            $ukuranBaris = $ukuranList[$i] ?? null;
            $ukuranBaris = $ukuranBaris !== '' ? $ukuranBaris : null;

            if ($produk->punya_ukuran) {
                $ukuranValid = collect($produk->daftar_ukuran)->pluck('nama')->map(fn ($n) => strtolower($n));

                if (!$ukuranBaris) {
                    $errors["ukuran.$i"] = "Pilih ukuran untuk produk \"{$produk->nama_produk}\".";
                } elseif (!$ukuranValid->contains(strtolower($ukuranBaris))) {
                    $errors["ukuran.$i"] = "Ukuran yang dipilih untuk \"{$produk->nama_produk}\" tidak valid.";
                }
            } else {
                // Produk tanpa varian ukuran gak butuh (dan gak boleh) ukuran.
                $ukuranBaris = null;
            }

            $kunci = $produkId . '|' . strtolower((string) $ukuranBaris);
            if (isset($kombinasiSudahAda[$kunci])) {
                $errors["produk_id.$i"] = "Produk \"{$produk->nama_produk}\"" . ($ukuranBaris ? " ukuran \"$ukuranBaris\"" : '') . " sudah dipilih di baris lain.";
            }
            $kombinasiSudahAda[$kunci] = true;
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
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
            $produkBiasa = Produk::reguler()
                ->where('status_aktif', true)
                ->with('produkUkurans.ukuran')
                ->orderBy('nama_produk')
                ->get();

            return view('pages.bundling.edit', compact('bundling', 'produkBiasa'));
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
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'produk_id' => 'required|array|min:1',
                'produk_id.*' => ['required', 'exists:produks,id'],
                'ukuran' => 'nullable|array',
                'ukuran.*' => 'nullable|string|max:50',
                'qty' => 'nullable|array',
                'qty.*' => 'nullable|integer|min:1',
            ]);

            $this->validasiUkuranBundling($request);

            DB::transaction(function () use ($request, $bundling) {

                $dataUpdate = [
                    'nama_bundling' => $request->nama_bundling,
                    'harga' => $request->harga,
                    'deskripsi' => $request->deskripsi,
                    'jenis_produk' => 'biasa',
                    'total_qty_khusus' => null,
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
                        'ukuran' => !empty($request->ukuran[$i]) ? $request->ukuran[$i] : null,
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