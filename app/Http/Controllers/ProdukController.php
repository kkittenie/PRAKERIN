<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProdukController extends Controller
{
    /** Folder foto varian (terpisah dari folder foto produk supaya tidak ikut terhapus). */
    private const FOTO_VARIAN_PATH = 'produk-varian';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Produk::with([
                    'kategori',
                    'produkVarians',
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
                    ->orderColumn('kategori.nama_kategori', function ($q, $order) {
                        $q->join('kategori_produks', 'produks.kategori_id', '=', 'kategori_produks.id')
                          ->orderBy('kategori_produks.nama_kategori', $order)
                          ->select('produks.*');
                    })
                    ->addColumn('foto', fn($item) => view('pages.produk.partials.foto', compact('item'))->render())
                    ->editColumn('nama_produk', fn($item) => view('pages.produk.partials.nama', compact('item'))->render())
                    ->addColumn('varian_rasa_html', fn($item) => view('pages.produk.partials.varian_rasa', compact('item'))->render())
                    ->addColumn('harga_jual_html', fn($item) => view('pages.produk.partials.harga', compact('item'))->render())
                    ->addColumn('stok_html', fn($item) => view('pages.produk.partials.stok', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.produk.partials.status', compact('item'))->render())
                    ->addColumn('ketersediaan_html', fn($item) => view('pages.produk.partials.ketersediaan', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.produk.partials.aksi', compact('item'))->render())
                    ->rawColumns(['foto', 'nama_produk', 'varian_rasa_html', 'harga_jual_html', 'stok_html', 'status_html', 'ketersediaan_html', 'aksi'])
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
            $punyaVarian = (bool) (int) old('punya_varian', 0);
            $varianBaris = $this->barisDariOld(old('varian_baris', []));

            return view('pages.produk.create', compact('kategori', 'punyaVarian', 'varianBaris'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validasiProduk($request);

            $baris = $this->ambilBarisVarian($request, $validated['punya_varian']);
            $this->pastikanBarisVarianValid($baris);

            $validated = $this->terapkanDataProduk($validated, $baris);

            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('produk/' . $validated['kode_produk'], 'public');
            }

            $produk = DB::transaction(function () use ($validated, $baris) {
                $produk = Produk::create($validated);
                $this->simpanBarisVarian($produk, $baris);
                return $produk;
            });

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produk)
                ->withProperties([
                    'kode_produk' => $validated['kode_produk'],
                    'nama_produk' => $validated['nama_produk'],
                    'kategori_id' => $validated['kategori_id'],
                    'harga_jual' => $validated['harga_jual'],
                    'stok' => $validated['stok'],
                    'jumlah_varian' => $baris->count(),
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
            $produk->load(['kategori', 'produkVarians']);

            return view('pages.produk.show', compact('produk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Produk $produk)
    {
        try {
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();
            $produk->load('produkVarians');

            if (old('varian_baris') !== null) {
                $varianBaris = $this->barisDariOld(old('varian_baris', []), $produk);
            } else {
                $varianBaris = $produk->produkVarians
                    ->map(fn (ProdukVarian $v) => $this->barisUntukForm($v))
                    ->values();
            }

            $punyaVarian = (bool) (int) old('punya_varian', $produk->produkVarians->isNotEmpty() ? 1 : 0);

            return view('pages.produk.edit', compact('produk', 'kategori', 'punyaVarian', 'varianBaris'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Produk $produk)
    {
        try {
            $validated = $this->validasiProduk($request, $produk);

            $baris = $this->ambilBarisVarian($request, $validated['punya_varian']);
            $this->pastikanBarisVarianValid($baris);

            $validated = $this->terapkanDataProduk($validated, $baris);

            $oldKode = $produk->kode_produk;
            $newKode = $validated['kode_produk'];

            if ($request->hasFile('foto')) {
                if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                    Storage::disk('public')->deleteDirectory('produk/' . $oldKode);
                }

                $validated['foto'] = $request
                    ->file('foto')
                    ->store('produk/' . $newKode, 'public');
            } elseif ($oldKode !== $newKode && $produk->foto) {
                $oldFolder = 'produk/' . $oldKode;
                $newFolder = 'produk/' . $newKode;

                if (Storage::disk('public')->exists($oldPath = $produk->foto)) {
                    $filename = basename($oldPath);
                    $newPath = $newFolder . '/' . $filename;
                    Storage::disk('public')->move($oldPath, $newPath);
                    $validated['foto'] = $newPath;
                }
                Storage::disk('public')->deleteDirectory($oldFolder);
            }

            DB::transaction(function () use ($produk, $validated, $baris) {
                $produk->update($validated);
                $this->simpanBarisVarian($produk, $baris);
            });

            activity()
                ->causedBy(auth()->user())
                ->performedOn($produk)
                ->withProperties([
                    'kode_produk' => $validated['kode_produk'],
                    'nama_produk' => $validated['nama_produk'],
                    'kategori_id' => $validated['kategori_id'],
                    'harga_jual' => $validated['harga_jual'],
                    'stok' => $validated['stok'],
                    'jumlah_varian' => $baris->count(),
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

                Storage::disk('public')->deleteDirectory(self::FOTO_VARIAN_PATH . '/' . $produk->id);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($produk)
                    ->withProperties([
                        'kode_produk' => $produk->kode_produk,
                        'nama_produk' => $produk->nama_produk,
                        'kategori_id' => $produk->kategori_id,
                        'harga_jual' => $produk->harga_jual,
                        'stok' => $produk->stok,
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
    // Helper varian
    // ------------------------------------------------------------------

    /**
     * Validasi field produk + baris varian. Mengembalikan data produk yang
     * sudah tervalidasi, plus flag 'punya_varian' (bool).
     */
    private function validasiProduk(Request $request, ?Produk $produk = null): array
    {
        $punyaVarian = $request->boolean('punya_varian');

        // Kalau produk tidak punya varian, abaikan semua baris varian yang ikut terkirim.
        if (!$punyaVarian) {
            $request->merge(['varian_baris' => []]);
        }

        $kodeRule = $produk
            ? ['required', 'max:50', Rule::unique('produks', 'kode_produk')->ignore($produk->id)]
            : ['required', 'max:50', 'unique:produks,kode_produk'];

        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_produks,id',
            'kode_produk' => $kodeRule,
            'nama_produk' => 'required|max:100',
            'deskripsi' => 'nullable|string|max:1000',
            'punya_varian' => 'nullable|boolean',
            'harga_jual' => 'nullable|required_if:punya_varian,0|numeric|min:0',
            'harga_modal' => 'nullable|required_if:punya_varian,0|numeric|min:0',
            'stok' => [
                'nullable',
                Rule::requiredIf(fn () => !$punyaVarian),
                'numeric',
                'min:0',
            ],
            'stok_minimum' => 'nullable|numeric|min:0',
            'status_aktif' => 'required|boolean',
            'status_ketersediaan' => ['required', Rule::in(['stok_ada', 'perlu_diproduksi'])],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'varian_baris' => 'nullable|array|required_if:punya_varian,1',
            'varian_baris.*.id' => 'nullable|integer',
            'varian_baris.*.ukuran' => 'nullable|string|max:50',
            'varian_baris.*.variasi' => 'nullable|string|max:100',
            'varian_baris.*.harga_modal' => 'required_with:varian_baris|numeric|min:0',
            'varian_baris.*.harga_jual' => 'required_with:varian_baris|numeric|min:0',
            'varian_baris.*.stok' => 'nullable|integer|min:0',
            'varian_baris.*.stok_minimum' => 'nullable|integer|min:0',
            'varian_baris.*.status_aktif' => 'nullable|boolean',
            'varian_baris.*.foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'varian_baris.required_if' => 'Tambahkan minimal 1 varian.',
            'varian_baris.*.harga_modal.required_with' => 'Harga modal varian wajib diisi.',
            'varian_baris.*.harga_jual.required_with' => 'Harga jual varian wajib diisi.',
            'varian_baris.*.foto.image' => 'Foto varian harus berupa gambar.',
            'varian_baris.*.foto.max' => 'Foto varian maksimal 2 MB.',
        ]);

        unset($validated['varian_baris']);
        $validated['punya_varian'] = $punyaVarian;

        return $validated;
    }

    /** Rapikan baris varian dari request jadi collection yang siap disimpan. */
    private function ambilBarisVarian(Request $request, bool $punyaVarian): Collection
    {
        if (!$punyaVarian) {
            return collect();
        }

        $hasil = collect();

        foreach ((array) $request->input('varian_baris', []) as $kunci => $b) {
            $ukuran = trim((string) ($b['ukuran'] ?? ''));
            $variasi = trim((string) ($b['variasi'] ?? ''));

            $hasil->push([
                'id' => !empty($b['id']) ? (int) $b['id'] : null,
                'ukuran' => $ukuran !== '' ? $ukuran : null,
                'variasi' => $variasi !== '' ? $variasi : null,
                'harga_modal' => (float) ($b['harga_modal'] ?? 0),
                'harga_jual' => (float) ($b['harga_jual'] ?? 0),
                'stok' => max(0, (int) ($b['stok'] ?? 0)),
                'stok_minimum' => max(0, (int) ($b['stok_minimum'] ?? 0)),
                'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'file' => $request->file("varian_baris.$kunci.foto"),
            ]);
        }

        return $hasil->values();
    }

    /** Tiap baris wajib punya ukuran/variasi, dan kombinasinya tidak boleh dobel. */
    private function pastikanBarisVarianValid(Collection $baris): void
    {
        $dipakai = [];

        foreach ($baris as $i => $b) {
            $nomor = $i + 1;

            if ($b['ukuran'] === null && $b['variasi'] === null) {
                throw ValidationException::withMessages([
                    'varian_baris' => "Baris varian ke-{$nomor}: isi minimal Ukuran atau Variasi.",
                ]);
            }

            $kunci = mb_strtolower(($b['ukuran'] ?? '') . '|' . ($b['variasi'] ?? ''));

            if (isset($dipakai[$kunci])) {
                throw ValidationException::withMessages([
                    'varian_baris' => "Baris varian ke-{$nomor}: kombinasi ukuran & variasi ini sudah ada di baris lain.",
                ]);
            }

            $dipakai[$kunci] = true;
        }
    }

    /**
     * Isi kolom di tabel produks. Kalau produk punya varian, harga produk = harga
     * varian termurah dan stok produk = total stok semua varian (dipakai halaman
     * lain yang masih membaca kolom produks).
     */
    private function terapkanDataProduk(array $validated, Collection $baris): array
    {
        $punyaVarian = $validated['punya_varian'];
        unset($validated['punya_varian']);

        if ($punyaVarian && $baris->isNotEmpty()) {
            $termurah = $baris->sortBy('harga_jual')->first();

            $validated['punya_ukuran'] = $baris->contains(fn ($b) => $b['ukuran'] !== null);
            $validated['punya_varian_rasa'] = $baris->contains(fn ($b) => $b['variasi'] !== null);
            $validated['harga_jual'] = $termurah['harga_jual'];
            $validated['harga_modal'] = $termurah['harga_modal'];
            $validated['stok'] = (int) $baris->sum('stok');
            $validated['stok_minimum'] = (int) $baris->sum('stok_minimum');
        } else {
            $validated['punya_ukuran'] = false;
            $validated['punya_varian_rasa'] = false;
            $validated['harga_jual'] = (float) ($validated['harga_jual'] ?? 0);
            $validated['harga_modal'] = (float) ($validated['harga_modal'] ?? 0);
            $validated['stok'] = (int) ($validated['stok'] ?? 0);
            $validated['stok_minimum'] = (int) ($validated['stok_minimum'] ?? 0);
        }

        $validated['harga_besar'] = null;
        $validated['harga_kecil'] = null;
        $validated['stok_besar'] = 0;
        $validated['stok_kecil'] = 0;

        $validated['status_ketersediaan'] = $validated['stok'] <= 0
            ? 'perlu_diproduksi'
            : 'stok_ada';

        return $validated;
    }

    /**
     * Simpan baris varian: yang punya id di-update, yang baru dibuat, dan yang
     * tidak ada lagi di form dihapus. Kalau $baris kosong, semua varian produk dihapus.
     */
    private function simpanBarisVarian(Produk $produk, Collection $baris): void
    {
        $idDipakai = [];

        foreach ($baris as $urutan => $b) {
            $varian = $b['id']
                ? ProdukVarian::where('produk_id', $produk->id)->where('id', $b['id'])->first()
                : null;

            $foto = $varian?->foto;
            if ($b['file']) {
                $this->hapusFotoVarian($foto);
                $foto = $b['file']->store(self::FOTO_VARIAN_PATH . '/' . $produk->id, 'public');
            }

            $data = [
                'ukuran' => $b['ukuran'],
                'variasi' => $b['variasi'],
                'harga_modal' => $b['harga_modal'],
                'harga_jual' => $b['harga_jual'],
                'stok' => $b['stok'],
                'stok_minimum' => $b['stok_minimum'],
                'foto' => $foto,
                'status_aktif' => $b['status_aktif'],
                'status_ketersediaan' => $b['stok'] > 0 ? 'stok_ada' : 'perlu_diproduksi',
                'urutan' => $urutan,
            ];

            if ($varian) {
                $varian->update($data);
            } else {
                $varian = ProdukVarian::create($data + ['produk_id' => $produk->id]);
            }

            $idDipakai[] = $varian->id;
        }

        ProdukVarian::where('produk_id', $produk->id)
            ->whereNotIn('id', $idDipakai)
            ->get()
            ->each(function (ProdukVarian $v) {
                $this->hapusFotoVarian($v->foto);
                $v->delete();
            });
    }

    /** Hanya hapus file yang memang milik varian (bukan file bersama dari master lama). */
    private function hapusFotoVarian(?string $path): void
    {
        if ($path && str_starts_with($path, self::FOTO_VARIAN_PATH . '/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Bentuk satu baris untuk tabel varian di form. */
    private function barisUntukForm(ProdukVarian $v): array
    {
        return [
            'id' => $v->id,
            'ukuran' => $v->ukuran ?? '',
            'variasi' => $v->variasi ?? '',
            'harga_modal' => (int) $v->harga_modal,
            'harga_jual' => (int) $v->harga_jual,
            'stok' => (int) $v->stok,
            'stok_minimum' => (int) $v->stok_minimum,
            'foto_url' => $v->foto_url,
            'status_aktif' => (bool) $v->status_aktif,
        ];
    }

    /** Kembalikan isian form dari old input (setelah validasi gagal). Foto lama diambil lagi dari DB lewat id. */
    private function barisDariOld($oldBaris, ?Produk $produk = null): Collection
    {
        $varianLama = $produk ? $produk->produkVarians->keyBy('id') : collect();

        return collect($oldBaris)->map(function ($b) use ($varianLama) {
            $id = !empty($b['id']) ? (int) $b['id'] : '';

            return [
                'id' => $id,
                'ukuran' => $b['ukuran'] ?? '',
                'variasi' => $b['variasi'] ?? '',
                'harga_modal' => $b['harga_modal'] ?? '',
                'harga_jual' => $b['harga_jual'] ?? '',
                'stok' => $b['stok'] ?? 0,
                'stok_minimum' => $b['stok_minimum'] ?? 0,
                'foto_url' => $id !== '' ? optional($varianLama->get($id))->foto_url : null,
                'status_aktif' => filter_var($b['status_aktif'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        })->values();
    }
}