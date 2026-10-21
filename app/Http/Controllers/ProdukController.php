<?php

namespace App\Http\Controllers;

use App\Models\DetailProduk;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\VarianProduk;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProdukController extends Controller
{
    /**
     * Folder foto detail produk (varian/rasa). Tiap detail punya subfolder sesuai kodenya:
     * varian-rasa/PRD0001-01/namafile.jpg
     */
    private const FOTO_DETAIL_PATH = 'varian-rasa';

    /** Folder lama (per id produk), masih dikenali supaya foto lama tetap bisa dibersihkan. */
    private const FOTO_DETAIL_PATH_LAMA = 'produk-varian';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Produk::with(['kategori', 'detailProduks', 'varianProduks'])->when($request->filled('kategori_id'), function ($q) use ($request) {
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
                            $qd->where('kode_produk', 'like', "%{$keyword}%");
                        });
                    })
                    ->orderColumn('kategori.nama_kategori', function ($q, $order) {
                        $q->join('kategori_produks', 'produks.kategori_id', '=', 'kategori_produks.id')
                          ->orderBy('kategori_produks.nama_kategori', $order)
                          ->select('produks.*');
                    })
                    // Harga, stok, dan ketersediaan dihitung dari detail produk, jadi urutannya juga dari detail produk.
                    ->orderColumn('harga_jual_html', function ($q, $order) {
                        $q->orderBy(
                            DetailProduk::selectRaw('MIN(harga_jual)')->whereColumn('detail_produks.produk_id', 'produks.id'),
                            $order
                        );
                    })
                    ->orderColumn('stok_html', function ($q, $order) {
                        $q->orderBy(
                            DetailProduk::selectRaw('COALESCE(SUM(stok), 0)')->whereColumn('detail_produks.produk_id', 'produks.id'),
                            $order
                        );
                    })
                    ->orderColumn('ketersediaan_html', function ($q, $order) {
                        $q->orderBy(
                            DetailProduk::selectRaw('COALESCE(SUM(CASE WHEN status_aktif = 1 AND stok > 0 THEN 1 ELSE 0 END), 0)')
                                ->whereColumn('detail_produks.produk_id', 'produks.id'),
                            $order
                        );
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
            $baru = !$produk->status_aktif;
            $produk->update(['status_aktif' => $baru]);
            $produk->detailProduks()->update(['status_aktif' => $baru]);
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
            $varianBaris = $this->varianDariOld(old('varian_baris', []));

            return view('pages.produk.create', compact('kategori', 'detailBaris', 'varianBaris'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validasiProduk($request);

            $baris = $this->ambilBarisDetail($request);
            $varian = $this->ambilBarisVarian($request);
            if ($request->has('status_aktif')) {
                $statusInput = $request->boolean('status_aktif');
                $validated['status_aktif'] = $statusInput;
                if (!$statusInput) {
                    $baris = $baris->map(function ($b) {
                        $b['status_aktif'] = false;
                        return $b;
                    });
                }
            } else {
                $validated['status_aktif'] = $this->statusProdukDariBaris($baris);
            }
            $this->pastikanBarisValid($baris);
            $this->pastikanVarianValid($varian, $baris);

            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('produk', 'public');
            }

            $global = $request->input('mode_stok') === 'global';

            $produk = DB::transaction(function () use ($validated, $baris, $varian, $global) {
                $produk = Produk::create($validated);
                $this->simpanBarisDetail($produk, $baris);
                $this->simpanVarianGlobal($produk, $varian);
                $this->sinkronFotoUtama($produk, null, $global);
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
            $produk->load(['kategori', 'detailProduks', 'varianProduks']);

            return view('pages.produk.show', compact('produk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Produk $produk)
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();
            $produk->load(['detailProduks', 'varianProduks']);

            $varianBaris = old('varian_baris') !== null
                ? $this->varianDariOld(old('varian_baris', []), $produk)
                : $produk->varianProduks->map(fn (VarianProduk $v) => $this->varianUntukForm($v))->values();

            if (old('detail_baris') !== null) {
                $detailBaris = $this->barisDariOld(old('detail_baris', []), $produk);
            } elseif ($produk->detailProduks->isNotEmpty()) {
                $detailBaris = $produk->detailProduks
                    ->map(fn (DetailProduk $d) => $this->barisUntukForm($d))
                    ->values();
            } else {
                // Produk yang belum punya detail: satu baris kosong untuk diisi.
                $detailBaris = collect([[
                    'id' => '',
                    'kode' => '',
                    'ukuran' => '',
                    'variasi' => '',
                    'harga_modal' => '',
                    'harga_jual' => '',
                    'stok' => 0,
                    'stok_minimum' => 0,
                    'foto_url' => null,
                    'status_aktif' => true,
                ]]);
            }

            // Foto utama untuk prefill; kalau belum ada, pakai foto detail pertama (data lama).
            $fotoUtamaUrl = $produk->foto
                ? asset('storage/' . $produk->foto)
                : $produk->detailProduks->first(fn (DetailProduk $d) => $d->foto)?->foto_url;

            return view('pages.produk.edit', compact('produk', 'kategori', 'detailBaris', 'varianBaris', 'fotoUtamaUrl'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Produk $produk)
    {
        try {
            $validated = $this->validasiProduk($request);

            $baris = $this->ambilBarisDetail($request);
            $varian = $this->ambilBarisVarian($request);
            if ($request->has('status_aktif')) {
                $statusInput = $request->boolean('status_aktif');
                $validated['status_aktif'] = $statusInput;
                if (!$statusInput) {
                    $baris = $baris->map(function ($b) {
                        $b['status_aktif'] = false;
                        return $b;
                    });
                }
            } else {
                $validated['status_aktif'] = $this->statusProdukDariBaris($baris);
            }
            $this->pastikanBarisValid($baris, $produk);
            $this->pastikanVarianValid($varian, $baris, $produk);

            $fotoLama = $produk->foto;

            if ($request->hasFile('foto')) {
                if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->delete($produk->foto);
                }

                $validated['foto'] = $request->file('foto')->store('produk', 'public');
            }

            $global = $request->input('mode_stok') === 'global';
            $fotoBaru = $request->hasFile('foto');

            DB::transaction(function () use ($produk, $validated, $baris, $varian, $global, $fotoBaru, $fotoLama) {
                $produk->update($validated);
                $this->simpanBarisDetail($produk, $baris);
                $this->simpanVarianGlobal($produk, $varian);
                $this->sinkronFotoUtama($produk, $fotoBaru ? $fotoLama : null, $global);
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
                if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->delete($produk->foto);
                }

                foreach ($produk->detailProduks as $detail) {
                    $this->hapusFotoDetail($detail->foto);
                }

                foreach ($produk->varianProduks as $varian) {
                    $this->hapusFotoDetail($varian->foto);
                }

                Storage::disk('public')->deleteDirectory(self::FOTO_DETAIL_PATH_LAMA . '/' . $produk->id);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'nama_produk' => $produk->nama_produk,
                        'kategori_id' => $produk->kategori_id,
                        'kode_detail' => $produk->detailProduks()->pluck('kode_produk')->all(),
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
            'dijual_satuan' => 'required|boolean',
            'status_aktif' => 'nullable|boolean',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'mode_stok' => 'nullable|in:global,varian',

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

            'varian_baris' => 'nullable|array',
            'varian_baris.*.id' => 'nullable|integer',
            'varian_baris.*.kode' => 'nullable|string|max:60',
            'varian_baris.*.ukuran' => 'nullable|string|max:50',
            'varian_baris.*.variasi' => 'nullable|string|max:100',
            'varian_baris.*.status_aktif' => 'nullable|boolean',
            'varian_baris.*.foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'detail_baris.required' => 'Tambahkan minimal 1 detail produk.',
            'detail_baris.min' => 'Tambahkan minimal 1 detail produk.',
            'detail_baris.*.kode.required' => 'Kode detail produk wajib diisi.',
            'detail_baris.*.kode.max' => 'Kode detail produk maksimal 60 karakter.',
            'detail_baris.*.harga_modal.required' => 'Harga modal wajib diisi.',
            'detail_baris.*.harga_jual.required' => 'Harga jual wajib diisi.',
            'detail_baris.*.foto.image' => 'Foto detail produk harus berupa gambar.',
            'detail_baris.*.foto.max' => 'Foto detail produk maksimal 2 MB.',
            'varian_baris.*.kode.max' => 'Kode varian maksimal 60 karakter.',
            'varian_baris.*.foto.image' => 'Foto varian harus berupa gambar.',
            'varian_baris.*.foto.max' => 'Foto varian maksimal 2 MB.',
        ]);

        unset($validated['detail_baris'], $validated['varian_baris'], $validated['mode_stok']);

        return $validated;
    }

    /**
     * Status produk tidak lagi punya isian sendiri: produk aktif kalau minimal satu detail aktif.
     * Di Stok Bersama itu sama dengan toggle Status di form.
     */
    private function statusProdukDariBaris(Collection $baris): bool
    {
        return $baris->contains(fn ($b) => (bool) $b['status_aktif']);
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

        if ($request->input('mode_stok') === 'global') {
            $hasil = $hasil->take(1)->map(function ($b) {
                $b['ukuran'] = null;
                $b['variasi'] = null;

                return $b;
            });
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
        $bentrok = DetailProduk::whereIn('kode_produk', $baris->pluck('kode')->all())
            ->when($produk, fn ($q) => $q->where('produk_id', '!=', $produk->id))
            ->pluck('kode_produk')
            ->merge(
                VarianProduk::whereIn('kode_produk', $baris->pluck('kode')->all())
                    ->when($produk, fn ($q) => $q->where('produk_id', '!=', $produk->id))
                    ->pluck('kode_produk')
            )
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
                $foto = $b['file']->store($this->folderFotoDetail($b['kode']), 'public');
            } elseif ($foto) {
                // Kode berubah -> foto ikut pindah ke folder kode yang baru.
                $foto = $this->pindahkanFotoDetail($foto, $b['kode']);
            }

            $data = [
                'kode_produk' => $b['kode'],
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

    /**
     * Baris varian untuk produk Stok Bersama (global), mis. topping.
     * Hanya dipakai kalau mode stok = global; di mode lain hasilnya kosong
     * (varian lama produk ini ikut terhapus saat disimpan).
     */
    private function ambilBarisVarian(Request $request): Collection
    {
        if ($request->input('mode_stok') !== 'global') {
            return collect();
        }

        $hasil = collect();

        foreach ((array) $request->input('varian_baris', []) as $kunci => $b) {
            $ukuran = trim((string) ($b['ukuran'] ?? ''));
            $variasi = trim((string) ($b['variasi'] ?? ''));
            $kode = trim((string) ($b['kode'] ?? ''));
            $file = $request->file("varian_baris.$kunci.foto");

            // Baris yang benar-benar kosong (belum diisi apa pun) dilewati.
            if ($ukuran === '' && $variasi === '' && $kode === '' && ! $file && empty($b['id'])) {
                continue;
            }

            $hasil->push([
                'id' => !empty($b['id']) ? (int) $b['id'] : null,
                'kode' => $kode,
                'ukuran' => $ukuran !== '' ? $ukuran : null,
                'variasi' => $variasi !== '' ? $variasi : null,
                'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'file' => $file,
            ]);
        }

        return $hasil->values();
    }

    /** Tiap varian wajib punya ukuran atau variasi; kombinasi dan kode tidak boleh dobel. */
    private function pastikanVarianValid(Collection $varian, Collection $barisDetail, ?Produk $produk = null): void
    {
        if ($varian->isEmpty()) {
            return;
        }

        $kombinasiDipakai = [];
        $kodeDipakai = $barisDetail->pluck('kode')->map(fn ($k) => mb_strtolower($k))->flip()->all();

        foreach ($varian as $i => $v) {
            $nomor = $i + 1;

            if ($v['ukuran'] === null && $v['variasi'] === null) {
                throw ValidationException::withMessages([
                    'varian_baris' => "Varian ke-{$nomor}: isi minimal Ukuran atau Varian.",
                ]);
            }

            $kunci = mb_strtolower(($v['ukuran'] ?? '') . '|' . ($v['variasi'] ?? ''));
            if (isset($kombinasiDipakai[$kunci])) {
                throw ValidationException::withMessages([
                    'varian_baris' => "Varian ke-{$nomor}: kombinasi ukuran & varian ini sudah ada di baris lain.",
                ]);
            }
            $kombinasiDipakai[$kunci] = true;

            if ($v['kode'] !== '') {
                $kunciKode = mb_strtolower($v['kode']);
                if (isset($kodeDipakai[$kunciKode])) {
                    throw ValidationException::withMessages([
                        'varian_baris' => "Varian ke-{$nomor}: kode \"{$v['kode']}\" sudah dipakai di baris lain.",
                    ]);
                }
                $kodeDipakai[$kunciKode] = true;
            }
        }

        // Kode yang sudah dipakai produk lain (detail produk maupun varian produk lain),
        // atau detail produk ini sendiri kalau bukan produk global yang sama.
        $kodeBaru = $varian->pluck('kode')->filter()->all();

        $bentrok = DetailProduk::whereIn('kode_produk', $kodeBaru)
            ->when($produk, fn ($q) => $q->where('produk_id', '!=', $produk->id))
            ->pluck('kode_produk')
            ->merge(
                VarianProduk::whereIn('kode_produk', $kodeBaru)
                    ->when($produk, fn ($q) => $q->where('produk_id', '!=', $produk->id))
                    ->pluck('kode_produk')
            )
            ->map(fn ($k) => mb_strtolower($k))
            ->flip();

        foreach ($varian as $i => $v) {
            if ($v['kode'] !== '' && $bentrok->has(mb_strtolower($v['kode']))) {
                $nomor = $i + 1;
                throw ValidationException::withMessages([
                    'varian_baris' => "Varian ke-{$nomor}: kode \"{$v['kode']}\" sudah dipakai produk lain.",
                ]);
            }
        }
    }

    /**
     * Simpan varian produk global: yang punya id di-update, yang baru dibuat, dan yang
     * tidak ada lagi di form (atau semuanya, kalau mode bukan global) dihapus beserta fotonya.
     */
    private function simpanVarianGlobal(Produk $produk, Collection $varian): void
    {
        $idDipakai = [];

        foreach ($varian as $urutan => $v) {
            $model = $v['id']
                ? VarianProduk::where('produk_id', $produk->id)->where('id', $v['id'])->first()
                : null;

            $kode = $v['kode'] !== ''
                ? $v['kode']
                : ($model?->kode_produk ?: VarianProduk::buatKode($produk->id));

            $foto = $model?->foto;
            if ($v['file']) {
                $this->hapusFotoDetail($foto);
                $foto = $v['file']->store($this->folderFotoDetail($kode), 'public');
            } elseif ($foto) {
                $foto = $this->pindahkanFotoDetail($foto, $kode);
            }

            $data = [
                'kode_produk' => $kode,
                'ukuran' => $v['ukuran'],
                'variasi' => $v['variasi'],
                'status_aktif' => $v['status_aktif'],
                'foto' => $foto,
                'urutan' => $urutan,
            ];

            if ($model) {
                $model->update($data);
            } else {
                $model = VarianProduk::create($data + ['produk_id' => $produk->id]);
            }

            $idDipakai[] = $model->id;
        }

        VarianProduk::where('produk_id', $produk->id)
            ->whereNotIn('id', $idDipakai)
            ->get()
            ->each(function (VarianProduk $m) {
                $this->hapusFotoDetail($m->foto);
                $m->delete();
            });
    }

    /** Bentuk satu baris varian untuk tabel di form. */
    private function varianUntukForm(VarianProduk $v): array
    {
        return [
            'id' => $v->id,
            'kode' => (string) $v->kode_produk,
            'ukuran' => $v->ukuran ?? '',
            'variasi' => $v->variasi ?? '',
            'status_aktif' => (bool) $v->status_aktif,
            'foto_url' => $v->foto_url,
        ];
    }

    /** Kembalikan isian varian dari old input (setelah validasi gagal). Foto lama diambil lagi dari DB lewat id. */
    private function varianDariOld($oldBaris, ?Produk $produk = null): Collection
    {
        $lama = $produk ? $produk->varianProduks->keyBy('id') : collect();

        return collect($oldBaris)->map(function ($b) use ($lama) {
            $id = !empty($b['id']) ? (int) $b['id'] : '';

            return [
                'id' => $id,
                'kode' => $b['kode'] ?? '',
                'ukuran' => $b['ukuran'] ?? '',
                'variasi' => $b['variasi'] ?? '',
                'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'foto_url' => $id !== '' ? optional($lama->get($id))->foto_url : null,
            ];
        })->values();
    }

    /**
     * Foto utama produk (produks.foto) juga dipakai kasir lewat detail produk.
     * - Foto utama diganti: detail yang masih menunjuk ke file lama dikosongkan dulu.
     * - Mode Stok Bersama: detail pertama selalu memakai foto utama (hanya ada satu foto di form).
     */
    private function sinkronFotoUtama(Produk $produk, ?string $fotoLama, bool $global): void
    {
        if ($fotoLama) {
            DetailProduk::where('produk_id', $produk->id)->where('foto', $fotoLama)->update(['foto' => null]);
        }

        if (! $global || ! $produk->foto) {
            return;
        }

        $detail = DetailProduk::where('produk_id', $produk->id)->orderBy('urutan')->orderBy('id')->first();

        if ($detail && $detail->foto !== $produk->foto) {
            $this->hapusFotoDetail($detail->foto);
            $detail->update(['foto' => $produk->foto]);
        }
    }

    /** Folder foto untuk satu kode detail, mis. varian-rasa/PRD0001-01 (karakter aneh diganti "-"). */
    private function folderFotoDetail(string $kode): string
    {
        $aman = trim(preg_replace('/[^A-Za-z0-9_-]+/', '-', $kode), '-');

        return self::FOTO_DETAIL_PATH . '/' . ($aman !== '' ? $aman : 'tanpa-kode');
    }

    /**
     * True kalau foto ini milik satu detail saja (ada di subfolder kode atau folder lama per produk).
     * File lepas langsung di varian-rasa/ adalah file bersama dari master lama, jadi tidak boleh dihapus/dipindah.
     */
    private function fotoMilikDetail(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        if (str_starts_with($path, self::FOTO_DETAIL_PATH_LAMA . '/')) {
            return true;
        }

        return str_starts_with($path, self::FOTO_DETAIL_PATH . '/') && substr_count($path, '/') >= 2;
    }

    /** Pindahkan foto ke folder kode yang sekarang; mengembalikan path foto (baru atau tetap). */
    private function pindahkanFotoDetail(string $foto, string $kode): string
    {
        if (! $this->fotoMilikDetail($foto) || ! Storage::disk('public')->exists($foto)) {
            return $foto;
        }

        $folderBaru = $this->folderFotoDetail($kode);
        if (dirname($foto) === $folderBaru) {
            return $foto;
        }

        $pathBaru = $folderBaru . '/' . basename($foto);
        Storage::disk('public')->move($foto, $pathBaru);
        $this->hapusFolderKosong(dirname($foto));

        return $pathBaru;
    }

    /** Hanya hapus file yang memang milik detail produk (bukan file bersama dari master lama). */
    private function hapusFotoDetail(?string $path): void
    {
        if (! $this->fotoMilikDetail($path) || ! Storage::disk('public')->exists($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
        $this->hapusFolderKosong(dirname($path));
    }

    private function hapusFolderKosong(string $folder): void
    {
        if (! in_array($folder, [self::FOTO_DETAIL_PATH, self::FOTO_DETAIL_PATH_LAMA, '.', ''], true)
            && empty(Storage::disk('public')->allFiles($folder))) {
            Storage::disk('public')->deleteDirectory($folder);
        }
    }

    /** Bentuk satu baris untuk tabel detail di form. */
    private function barisUntukForm(DetailProduk $d): array
    {
        return [
            'id' => $d->id,
            'kode' => (string) $d->kode_produk,
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