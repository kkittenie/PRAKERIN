<?php

namespace App\Http\Controllers;

use App\Models\DetailProduk;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProdukController extends Controller
{
    /** Folder foto detail produk (terpisah dari folder foto produk supaya tidak ikut terhapus). */
    private const FOTO_DETAIL_PATH = 'produk-varian';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Produk::with([
                    'kategori',
                    'detailProduks',
                    // relasi lama masih dimuat karena accessor di model Produk masih membacanya
                    'produkUkurans.ukuran',
                    'produkVarianRasas.varianRasa',
                ])->when($request->filled('kategori_id'), function ($q) use ($request) {
                    $q->where('kategori_id', $request->kategori_id);
                });

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->filterColumn('kategori.nama_kategori', function ($q, $keyword) {
                        $q->whereHas('kategori', function ($qk) use ($keyword) {
                            $qk->where('nama_kategori', 'like', "%{$keyword}%");
                        });
                    })
                    ->filterColumn('kode_html', function ($q, $keyword) {
                        $q->whereHas('detailProduks', function ($qd) use ($keyword) {
                            $qd->where('kode_barcode', 'like', "%{$keyword}%");
                        });
                    })
                    ->orderColumn('kategori.nama_kategori', function ($q, $order) {
                        $q->join('kategori_produks', 'produks.kategori_id', '=', 'kategori_produks.id')
                          ->orderBy('kategori_produks.nama_kategori', $order)
                          ->select('produks.*');
                    })
                    ->addColumn('foto', fn($item) => view('pages.produk.partials.foto', compact('item'))->render())
                    ->editColumn('nama_produk', fn($item) => view('pages.produk.partials.nama', compact('item'))->render())
                    ->addColumn('kode_html', fn($item) => view('pages.produk.partials.kode', compact('item'))->render())
                    ->addColumn('varian_rasa_html', fn($item) => view('pages.produk.partials.varian_rasa', compact('item'))->render())
                    ->addColumn('harga_jual_html', fn($item) => view('pages.produk.partials.harga', compact('item'))->render())
                    ->addColumn('stok_html', fn($item) => view('pages.produk.partials.stok', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.produk.partials.status', compact('item'))->render())
                    ->addColumn('ketersediaan_html', fn($item) => view('pages.produk.partials.ketersediaan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.produk.partials.aksi', compact('item'))->render())
                    ->rawColumns(['foto', 'nama_produk', 'kode_html', 'varian_rasa_html', 'harga_jual_html', 'stok_html', 'status_html', 'ketersediaan_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data produk.'], 500);
            }
        }

        $kategori = KategoriProduk::orderBy('nama_kategori')->get();

        return view('pages.produk.index', compact('kategori'));
    }

    public function toggleStatus(Produk $produk)
    {
        try {
            $produk->update(['status_aktif' => !$produk->status_aktif]);
            return redirect()->back()->with('success', 'Status produk berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();
            $detailBaris = $this->barisDariOld(old('detail_baris', []));

            return view('pages.produk.create', compact('kategori', 'detailBaris'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validasiProduk($request);

            $baris = $this->ambilBarisDetail($request);
            $this->pastikanBarisValid($baris);

            $validated = $this->terapkanDataProduk($validated, $baris);
            $validated['kode_produk'] = $this->buatKodeProduk();

            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('produk/' . $validated['kode_produk'], 'public');
            }

            $produk = DB::transaction(function () use ($validated, $baris) {
                $produk = Produk::create($validated);
                $this->simpanBarisDetail($produk, $baris);
                return $produk;
            });

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produk)
                ->withProperties([
                    'nama_produk' => $validated['nama_produk'],
                    'kategori_id' => $validated['kategori_id'],
                    'jumlah_detail' => $baris->count(),
                    'kode_detail' => $baris->pluck('kode')->all(),
                ])
                ->log('menambahkan produk');

            return redirect()
                ->route('produk.index')
                ->with('success', 'Produk berhasil ditambahkan.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(Produk $produk)
    {
        try {
            $produk->load(['kategori', 'detailProduks']);

            return view('pages.produk.show', compact('produk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Produk $produk)
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();
            $produk->load('detailProduks');

            if (old('detail_baris') !== null) {
                $detailBaris = $this->barisDariOld(old('detail_baris', []), $produk);
            } elseif ($produk->detailProduks->isNotEmpty()) {
                $detailBaris = $produk->detailProduks
                    ->map(fn (DetailProduk $d) => $this->barisUntukForm($d))
                    ->values();
            } else {
                // Produk lama yang belum punya baris detail: satu baris diisi dari data produk,
                // baru tersimpan sebagai detail produk kalau form ini disimpan.
                $detailBaris = collect([[
                    'id' => '',
                    'kode' => (string) $produk->kode_produk,
                    'ukuran' => '',
                    'variasi' => '',
                    'harga_modal' => (int) $produk->harga_modal,
                    'harga_jual' => (int) $produk->harga_jual,
                    'stok' => (int) $produk->stok,
                    'stok_minimum' => (int) $produk->stok_minimum,
                    'foto_url' => null,
                    'status_aktif' => true,
                ]]);
            }

            return view('pages.produk.edit', compact('produk', 'kategori', 'detailBaris'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Produk $produk)
    {
        try {
            $validated = $this->validasiProduk($request);

            $baris = $this->ambilBarisDetail($request);
            $this->pastikanBarisValid($baris, $produk);

            $validated = $this->terapkanDataProduk($validated, $baris);

            if ($request->hasFile('foto')) {
                if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->deleteDirectory('produk/' . $produk->kode_produk);
                }

                $validated['foto'] = $request
                    ->file('foto')
                    ->store('produk/' . $produk->kode_produk, 'public');
            }

            DB::transaction(function () use ($produk, $validated, $baris) {
                $produk->update($validated);
                $this->simpanBarisDetail($produk, $baris);
            });

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produk)
                ->withProperties([
                    'nama_produk' => $validated['nama_produk'],
                    'kategori_id' => $validated['kategori_id'],
                    'jumlah_detail' => $baris->count(),
                    'kode_detail' => $baris->pluck('kode')->all(),
                ])
                ->log('memperbarui produk');

            return redirect()
                ->route('produk.index')
                ->with('success', 'Produk berhasil diperbarui.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Produk $produk)
    {
        try {
            // Produk yang sudah pernah dicatat di Riwayat Produksi gak boleh
            // dihapus (FK produksis.produk_id di-restrict, sengaja biar
            // histori produksi lama gak ikut ilang/rusak). Cek dulu di sini
            // biar errornya jelas buat user, bukan raw SQL error, dan biar
            // activity log-nya gak kecatat "menghapus produk" kalau ternyata
            // penghapusannya gagal.
            if ($produk->produksi()->exists()) {
                return back()->with(
                    'error',
                    "Produk \"{$produk->nama_produk}\" tidak bisa dihapus karena sudah punya riwayat produksi. Nonaktifkan produk ini saja kalau tidak mau dipakai lagi."
                );
            }

            DB::transaction(function () use ($produk) {
                if ($produk->kode_produk) {
                    Storage::disk('public')->deleteDirectory('produk/' . $produk->kode_produk);
                } elseif ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->delete($produk->foto);
                }

                Storage::disk('public')->deleteDirectory(self::FOTO_DETAIL_PATH . '/' . $produk->id);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'nama_produk' => $produk->nama_produk,
                        'kategori_id' => $produk->kategori_id,
                        'kode_detail' => $produk->detailProduks()->pluck('kode_barcode')->all(),
                    ])
                    ->log('menghapus produk');

                $produk->delete();
            });

            return redirect()
                ->route('produk.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    // ------------------------------------------------------------------
    // Helper detail produk
    // ------------------------------------------------------------------

    /** Validasi informasi umum produk + baris detail. */
    private function validasiProduk(Request $request): array
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_produks,id',
            'nama_produk' => 'required|max:100',
            'deskripsi' => 'nullable|string|max:1000',
            'status_aktif' => 'required|boolean',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'detail_baris' => 'required|array|min:1',
            'detail_baris.*.id' => 'nullable|integer',
            'detail_baris.*.kode' => 'required|string|max:60',
            'detail_baris.*.ukuran' => 'nullable|string|max:50',
            'detail_baris.*.variasi' => 'nullable|string|max:100',
            'detail_baris.*.harga_modal' => 'required|numeric|min:0',
            'detail_baris.*.harga_jual' => 'required|numeric|min:0',
            'detail_baris.*.stok' => 'nullable|integer|min:0',
            'detail_baris.*.stok_minimum' => 'nullable|integer|min:0',
            'detail_baris.*.status_aktif' => 'nullable|boolean',
            'detail_baris.*.foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'detail_baris.required' => 'Tambahkan minimal 1 detail produk.',
            'detail_baris.min' => 'Tambahkan minimal 1 detail produk.',
            'detail_baris.*.kode.required' => 'Kode detail produk wajib diisi.',
            'detail_baris.*.kode.max' => 'Kode detail produk maksimal 60 karakter.',
            'detail_baris.*.harga_modal.required' => 'Harga modal wajib diisi.',
            'detail_baris.*.harga_jual.required' => 'Harga jual wajib diisi.',
            'detail_baris.*.foto.image' => 'Foto detail produk harus berupa gambar.',
            'detail_baris.*.foto.max' => 'Foto detail produk maksimal 2 MB.',
        ]);

        unset($validated['detail_baris']);

        return $validated;
    }

    /** Rapikan baris detail dari request jadi collection yang siap disimpan. */
    private function ambilBarisDetail(Request $request): Collection
    {
        $hasil = collect();

        foreach ((array) $request->input('detail_baris', []) as $kunci => $b) {
            $ukuran = trim((string) ($b['ukuran'] ?? ''));
            $variasi = trim((string) ($b['variasi'] ?? ''));

            $hasil->push([
                'id' => !empty($b['id']) ? (int) $b['id'] : null,
                'kode' => trim((string) ($b['kode'] ?? '')),
                'ukuran' => $ukuran !== '' ? $ukuran : null,
                'variasi' => $variasi !== '' ? $variasi : null,
                'harga_modal' => (float) ($b['harga_modal'] ?? 0),
                'harga_jual' => (float) ($b['harga_jual'] ?? 0),
                'stok' => max(0, (int) ($b['stok'] ?? 0)),
                'stok_minimum' => max(0, (int) ($b['stok_minimum'] ?? 0)),
                'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'file' => $request->file("detail_baris.$kunci.foto"),
            ]);
        }

        return $hasil->values();
    }

    /**
     * Kode dan kombinasi ukuran + variasi tidak boleh dobel (di form maupun di
     * produk lain). Kalau produk punya lebih dari satu baris, tiap baris wajib
     * punya ukuran atau variasi.
     */
    private function pastikanBarisValid(Collection $baris, ?Produk $produk = null): void
    {
        $kodeDipakai = [];
        $kombinasiDipakai = [];

        foreach ($baris as $i => $b) {
            $nomor = $i + 1;

            if ($baris->count() > 1 && $b['ukuran'] === null && $b['variasi'] === null) {
                throw ValidationException::withMessages([
                    'detail_baris' => "Baris ke-{$nomor}: isi minimal Ukuran atau Variasi.",
                ]);
            }

            $kunciKode = mb_strtolower($b['kode']);
            if (isset($kodeDipakai[$kunciKode])) {
                throw ValidationException::withMessages([
                    'detail_baris' => "Baris ke-{$nomor}: kode \"{$b['kode']}\" sudah dipakai di baris lain.",
                ]);
            }
            $kodeDipakai[$kunciKode] = true;

            $kunciKombinasi = mb_strtolower(($b['ukuran'] ?? '') . '|' . ($b['variasi'] ?? ''));
            if (isset($kombinasiDipakai[$kunciKombinasi])) {
                throw ValidationException::withMessages([
                    'detail_baris' => "Baris ke-{$nomor}: kombinasi ukuran & variasi ini sudah ada di baris lain.",
                ]);
            }
            $kombinasiDipakai[$kunciKombinasi] = true;
        }

        // Kode yang sudah dipakai produk lain
        $bentrok = DetailProduk::whereIn('kode_barcode', $baris->pluck('kode')->all())
            ->when($produk, fn ($q) => $q->where('produk_id', '!=', $produk->id))
            ->pluck('kode_barcode')
            ->map(fn ($k) => mb_strtolower($k))
            ->flip();

        foreach ($baris as $i => $b) {
            if ($bentrok->has(mb_strtolower($b['kode']))) {
                $nomor = $i + 1;
                throw ValidationException::withMessages([
                    'detail_baris' => "Baris ke-{$nomor}: kode \"{$b['kode']}\" sudah dipakai produk lain.",
                ]);
            }
        }
    }

    /**
     * Isi kolom di tabel produks. Kode, harga, dan stok yang sebenarnya ada di
     * detail produk; kolom di produks hanya salinan ringkas (harga termurah,
     * total stok) untuk halaman lain yang masih membacanya.
     */
    private function terapkanDataProduk(array $validated, Collection $baris): array
    {
        $termurah = $baris->sortBy('harga_jual')->first();
        $totalStok = (int) $baris->sum('stok');

        $validated['punya_ukuran'] = $baris->contains(fn ($b) => $b['ukuran'] !== null);
        $validated['punya_varian_rasa'] = $baris->contains(fn ($b) => $b['variasi'] !== null);
        $validated['harga_jual'] = $termurah['harga_jual'];
        $validated['harga_modal'] = $termurah['harga_modal'];
        $validated['stok'] = $totalStok;
        $validated['stok_minimum'] = (int) $baris->sum('stok_minimum');
        $validated['status_ketersediaan'] = $totalStok > 0 ? 'stok_ada' : 'perlu_diproduksi';

        $validated['harga_besar'] = null;
        $validated['harga_kecil'] = null;
        $validated['stok_besar'] = 0;
        $validated['stok_kecil'] = 0;

        return $validated;
    }

    /** Kode internal produk (tidak ditampilkan di form; kode yang dipakai kasir ada di detail produk). */
    private function buatKodeProduk(): string
    {
        $nomor = (int) Produk::max('id') + 1;

        do {
            $kode = 'PRD-' . str_pad($nomor, 4, '0', STR_PAD_LEFT);
            $nomor++;
        } while (Produk::where('kode_produk', $kode)->exists());

        return $kode;
    }

    /**
     * Simpan baris detail: yang punya id di-update, yang baru dibuat, dan yang
     * tidak ada lagi di form dihapus. Riwayat transaksi/produksi lama tetap aman
     * karena nama, kode, dan harganya sudah disalin saat kejadian.
     */
    private function simpanBarisDetail(Produk $produk, Collection $baris): void
    {
        $idDipakai = [];

        foreach ($baris as $urutan => $b) {
            $detail = $b['id']
                ? DetailProduk::where('produk_id', $produk->id)->where('id', $b['id'])->first()
                : null;

            $foto = $detail?->foto;
            if ($b['file']) {
                $this->hapusFotoDetail($foto);
                $foto = $b['file']->store(self::FOTO_DETAIL_PATH . '/' . $produk->id, 'public');
            }

            $data = [
                'kode_barcode' => $b['kode'],
                'ukuran' => $b['ukuran'],
                'variasi' => $b['variasi'],
                'harga_modal' => $b['harga_modal'],
                'harga_jual' => $b['harga_jual'],
                'stok' => $b['stok'],
                'stok_minimum' => $b['stok_minimum'],
                'foto' => $foto,
                'status_aktif' => $b['status_aktif'],
                'urutan' => $urutan,
            ];

            if ($detail) {
                $detail->update($data);
            } else {
                $detail = DetailProduk::create($data + ['produk_id' => $produk->id]);
            }

            $idDipakai[] = $detail->id;
        }

        DetailProduk::where('produk_id', $produk->id)
            ->whereNotIn('id', $idDipakai)
            ->get()
            ->each(function (DetailProduk $d) {
                $this->hapusFotoDetail($d->foto);
                $d->delete();
            });
    }

    /** Hanya hapus file yang memang milik detail produk (bukan file bersama dari master lama). */
    private function hapusFotoDetail(?string $path): void
    {
        if ($path && str_starts_with($path, self::FOTO_DETAIL_PATH . '/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Bentuk satu baris untuk tabel detail di form. */
    private function barisUntukForm(DetailProduk $d): array
    {
        return [
            'id' => $d->id,
            'kode' => (string) $d->kode_barcode,
            'ukuran' => $d->ukuran ?? '',
            'variasi' => $d->variasi ?? '',
            'harga_modal' => (int) $d->harga_modal,
            'harga_jual' => (int) $d->harga_jual,
            'stok' => (int) $d->stok,
            'stok_minimum' => (int) $d->stok_minimum,
            'foto_url' => $d->foto_url,
            'status_aktif' => (bool) $d->status_aktif,
        ];
    }

    /** Kembalikan isian form dari old input (setelah validasi gagal). Foto lama diambil lagi dari DB lewat id. */
    private function barisDariOld($oldBaris, ?Produk $produk = null): Collection
    {
        $detailLama = $produk ? $produk->detailProduks->keyBy('id') : collect();

        return collect($oldBaris)->map(function ($b) use ($detailLama) {
            $id = !empty($b['id']) ? (int) $b['id'] : '';

            return [
                'id' => $id,
                'kode' => $b['kode'] ?? '',
                'ukuran' => $b['ukuran'] ?? '',
                'variasi' => $b['variasi'] ?? '',
                'harga_modal' => $b['harga_modal'] ?? '',
                'harga_jual' => $b['harga_jual'] ?? '',
                'stok' => $b['stok'] ?? 0,
                'stok_minimum' => $b['stok_minimum'] ?? 0,
                'foto_url' => $id !== '' ? optional($detailLama->get($id))->foto_url : null,
                'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        })->values();
    }
}