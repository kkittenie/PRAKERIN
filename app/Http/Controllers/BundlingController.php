<?php

namespace App\Http\Controllers;

use App\Models\Bundling;
use App\Models\BundlingItem;
use App\Models\DetailProduk;
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
                $data = Bundling::with(['items.produk.detailProduks', 'items.detailProduk'])->get();

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
            $produkForm = $this->daftarProdukForm();

            return view('pages.bundling.create', compact('produkForm'));
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
            $this->normalisasiInput($request);
            $request->validate($this->aturanValidasi(), $this->pesanValidasi());
            $isiBundling = $this->susunIsiBundling($request);

            DB::transaction(function () use ($request, $isiBundling, &$bundling) {

                $bundling = Bundling::create([
                    'nama_bundling' => $request->nama_bundling,
                    'harga' => $request->harga,
                    'deskripsi' => $request->deskripsi,
                    'status_aktif' => $request->boolean('status_aktif', true),
                    'jenis_produk' => $request->jenis_produk,
                    'total_qty_khusus' => $request->jenis_produk === Bundling::JENIS_BEBAS ? (int) $request->total_qty_khusus : null,
                    'gambar' => null,
                ]);

                $kodeBundling = 'BDL' . str_pad((string) $bundling->id, 4, '0', STR_PAD_LEFT);
                $bundling->update(['kode_bundling' => $kodeBundling]);

                if ($request->hasFile('gambar')) {
                    $pathGambar = $request->file('gambar')->store('bundling/' . $kodeBundling, 'public');
                    $bundling->update(['gambar' => $pathGambar]);
                }

                $this->simpanIsiBundling($bundling, $isiBundling);
            });

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Bundling berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Daftar produk aktif untuk pilihan isi bundling (tetap maupun bebas), lengkap dengan varian
     * (detail produk) aktifnya dan daftar ukurannya. Produk yang tidak dijual satuan ikut ditampilkan
     * karena memang boleh jadi isi bundling.
     *  - $sertakanDetailId : detail yang sudah dipakai bundling tetap ini (tetap tampil walau sudah
     *                        dinonaktifkan, supaya baris lama tidak hilang saat diedit).
     *  - $sertakanProdukId : produk yang sudah dipakai bundling bebas ini (tetap tampil walau produknya
     *                        sudah nonaktif).
     * 'ukuran' dipakai bundling bebas: admin memilih ukuran, rasanya dipilih pembeli.
     */
    private function daftarProdukForm(array $sertakanDetailId = [], array $sertakanProdukId = []): array
    {
        $sertakanProdukId = array_values(array_unique(array_merge(
            $sertakanProdukId,
            $sertakanDetailId
                ? DetailProduk::whereIn('id', $sertakanDetailId)->pluck('produk_id')->all()
                : []
        )));

        return Produk::query()
            ->where(function ($q) use ($sertakanProdukId) {
                $q->where('status_aktif', true);
                if ($sertakanProdukId) {
                    $q->orWhereIn('id', $sertakanProdukId);
                }
            })
            ->with(['detailProduks' => function ($q) use ($sertakanDetailId) {
                $q->where(function ($w) use ($sertakanDetailId) {
                    $w->where('status_aktif', true);
                    if ($sertakanDetailId) {
                        $w->orWhereIn('id', $sertakanDetailId);
                    }
                });
            }])
            ->orderBy('nama_produk')
            ->get()
            ->filter(fn (Produk $p) => $p->detailProduks->isNotEmpty())
            ->map(fn (Produk $p) => [
                'id' => $p->id,
                'nama_produk' => $p->nama_produk,
                'dijual_satuan' => (bool) $p->dijual_satuan,
                'varian' => $p->detailProduks->map(fn (DetailProduk $d) => [
                    'id' => $d->id,
                    'ukuran' => $d->ukuran,
                    'nama' => $d->nama_lengkap ?: 'Standar',
                    'kode' => $d->kode_produk,
                    'stok' => (int) $d->stok,
                    'aktif' => (bool) $d->status_aktif,
                ])->values()->all(),
                'ukuran' => $this->daftarUkuranProduk($p->detailProduks),
            ])
            ->values()
            ->all();
    }

    /**
     * Daftar ukuran dari detail produk aktif, satu elemen per ukuran:
     * nilai = nama ukuran apa adanya ('' kalau produk tanpa ukuran), jumlah_rasa & stok = gabungan semua rasa aktif.
     */
    private function daftarUkuranProduk($detailProduks): array
    {
        $hasil = [];

        foreach ($detailProduks->where('status_aktif', true) as $d) {
            $nilai = trim((string) $d->ukuran);
            $kunci = mb_strtolower($nilai);

            if (!isset($hasil[$kunci])) {
                $hasil[$kunci] = ['nilai' => $nilai, 'nama' => $nilai !== '' ? $nilai : 'Tanpa ukuran', 'jumlah_rasa' => 0, 'stok' => 0, 'rasa' => []];
            }

            $hasil[$kunci]['jumlah_rasa']++;
            $hasil[$kunci]['stok'] += max(0, (int) $d->stok);
            $hasil[$kunci]['rasa'][] = $d->variasi ?: 'Standar';
        }

        return array_values($hasil);
    }

    private function normalisasiInput(Request $request): void
    {
        $request->merge(['harga' => (int) str_replace('.', '', (string) $request->harga)]);
    }

    private function aturanValidasi(): array
    {
        return [
            'nama_bundling' => 'required|string|max:150',
            'harga' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'status_aktif' => 'nullable|boolean',
            'jenis_produk' => 'required|in:' . Bundling::JENIS_TETAP . ',' . Bundling::JENIS_BEBAS,
            'total_qty_khusus' => 'required_if:jenis_produk,' . Bundling::JENIS_BEBAS . '|nullable|integer|min:1',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => ['required', 'exists:produks,id'],
            // Produk Tetap : id varian (detail produk). Produk Bebas : nama ukuran ('__tanpa__' = produk tanpa ukuran).
            'varian_isi' => 'required|array',
            'varian_isi.*' => ['required', 'string', 'max:100'],
            'qty' => 'nullable|array',
            'qty.*' => 'nullable|integer|min:1',
        ];
    }

    private function pesanValidasi(): array
    {
        return [
            'produk_id.required' => 'Tambahkan minimal satu produk.',
            'produk_id.*.required' => 'Pilih produk dari hasil pencarian.',
            'varian_isi.required' => 'Pilih varian atau ukuran untuk setiap produk.',
            'varian_isi.*.required' => 'Pilih varian atau ukuran untuk setiap produk.',
            'total_qty_khusus.required_if' => 'Isi total pilihan untuk bundling produk bebas.',
        ];
    }

    /** Penanda "produk tanpa ukuran" pada nilai ukuran yang dikirim form bundling bebas. */
    private const TANPA_UKURAN = '__tanpa__';

    /**
     * Susun isi bundling dari input form jadi daftar baris bersih, sekaligus memvalidasinya.
     *  - Produk Tetap : tiap baris = satu varian (detail produk) + qty. Varian harus milik produk di baris itu,
     *                   aktif (kecuali sudah dipakai bundling ini sebelumnya), dan tidak boleh kembar.
     *  - Produk Bebas : tiap baris = satu produk + ukuran. Ukuran harus punya minimal satu rasa aktif,
     *                   dan kombinasi produk + ukuran tidak boleh kembar. Rasa dipilih pembeli.
     * Hasil: [['produk_id', 'detail_produk_id', 'ukuran', 'varian_rasa', 'qty'], ...]
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function susunIsiBundling(Request $request, ?Bundling $bundling = null): array
    {
        $isBebas = $request->jenis_produk === Bundling::JENIS_BEBAS;
        $produkIds = array_values($request->produk_id ?? []);
        $pilihan = array_values($request->varian_isi ?? []);

        $produkMap = Produk::whereIn('id', $produkIds)->get()->keyBy('id');
        $errors = [];
        $baris = [];
        $sudahAda = [];

        if ($isBebas) {
            $detailPerProduk = DetailProduk::whereIn('produk_id', $produkIds)->get()->groupBy('produk_id');

            foreach ($produkIds as $i => $produkId) {
                $namaProduk = $produkMap->get($produkId)?->nama_produk ?? 'produk ini';
                $nilai = (string) ($pilihan[$i] ?? '');
                $nilai = $nilai === self::TANPA_UKURAN ? '' : trim($nilai);

                $cocok = ($detailPerProduk->get($produkId) ?? collect())
                    ->first(fn (DetailProduk $d) => $d->status_aktif && BundlingItem::samaUkuran($d->ukuran, $nilai));

                if (!$cocok) {
                    $errors["varian_isi.$i"] = "Ukuran yang dipilih untuk \"{$namaProduk}\" tidak valid atau tidak punya rasa aktif.";
                    continue;
                }

                $ukuran = trim((string) $cocok->ukuran) === '' ? null : trim((string) $cocok->ukuran);
                $kunci = $produkId . '|' . mb_strtolower((string) $ukuran);

                if (isset($sudahAda[$kunci])) {
                    $errors["varian_isi.$i"] = "Produk \"{$namaProduk}\" ukuran \"" . ($ukuran ?? 'tanpa ukuran') . "\" sudah dipilih di baris lain.";
                    continue;
                }
                $sudahAda[$kunci] = true;

                $baris[] = [
                    'produk_id' => (int) $produkId,
                    'detail_produk_id' => null,
                    'ukuran' => $ukuran,
                    'varian_rasa' => null,
                    'qty' => 1,
                ];
            }
        } else {
            $detailIds = array_map('intval', $pilihan);
            $detailMap = DetailProduk::whereIn('id', array_filter($detailIds))->get()->keyBy('id');
            $detailLama = $bundling ? $bundling->items()->pluck('detail_produk_id')->filter()->all() : [];

            foreach ($produkIds as $i => $produkId) {
                $detail = $detailMap->get($detailIds[$i] ?? 0);
                $namaProduk = $produkMap->get($produkId)?->nama_produk ?? 'produk ini';

                if (!$detail || (int) $detail->produk_id !== (int) $produkId) {
                    $errors["varian_isi.$i"] = "Varian yang dipilih untuk \"{$namaProduk}\" tidak valid.";
                    continue;
                }

                if (!$detail->status_aktif && !in_array($detail->id, $detailLama, true)) {
                    $errors["varian_isi.$i"] = "Varian \"{$detail->nama_lengkap}\" untuk \"{$namaProduk}\" sedang nonaktif.";
                    continue;
                }

                if (isset($sudahAda[$detail->id])) {
                    $errors["varian_isi.$i"] = "Produk \"{$namaProduk}\" varian \"" . ($detail->nama_lengkap ?: 'Standar') . "\" sudah dipilih di baris lain.";
                    continue;
                }
                $sudahAda[$detail->id] = true;

                $baris[] = [
                    'produk_id' => (int) $produkId,
                    'detail_produk_id' => (int) $detail->id,
                    'ukuran' => $detail->ukuran,
                    'varian_rasa' => $detail->variasi,
                    'qty' => max(1, (int) ($request->qty[$i] ?? 1)),
                ];
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return $baris;
    }

    /** Ganti seluruh isi bundling dengan baris hasil susunIsiBundling(). */
    private function simpanIsiBundling(Bundling $bundling, array $baris): void
    {
        foreach ($baris as $b) {
            BundlingItem::create([
                'bundling_id' => $bundling->id,
                'produk_id' => $b['produk_id'],
                'detail_produk_id' => $b['detail_produk_id'],
                'ukuran' => $b['ukuran'],
                'varian_rasa' => $b['varian_rasa'],
                'qty' => $b['qty'],
            ]);
        }
    }

    public function show(Bundling $bundling)
    {
        try {
            $bundling->load([
                'items.detailProduk',
                'items.produk.detailProduks',
            ]);

            return view('pages.bundling.show', compact('bundling'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(Bundling $bundling)
    {
        try {
            $bundling->load(['items.detailProduk', 'items.produk']);

            $detailTersimpan = $bundling->items->pluck('detail_produk_id')->filter()->all();
            $produkBebasTersimpan = $bundling->isBebas() ? $bundling->items->pluck('produk_id')->all() : [];

            $produkForm = $this->daftarProdukForm($detailTersimpan, $produkBebasTersimpan);

            // Bundling bebas menyimpan ukuran (rasa dipilih pembeli), bundling tetap menyimpan varian.
            $isiTersimpan = $bundling->items->map(fn (BundlingItem $item) => [
                'produk_id' => $item->produk_id,
                'detail_produk_id' => $item->detail_produk_id,
                'ukuran' => $item->ukuran,
                'qty' => $item->qty,
            ])->values()->all();

            return view('pages.bundling.edit', compact('bundling', 'produkForm', 'isiTersimpan'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Bundling $bundling)
    {
        try {
            $this->normalisasiInput($request);
            $request->validate($this->aturanValidasi(), $this->pesanValidasi());
            $isiBundling = $this->susunIsiBundling($request, $bundling);

            DB::transaction(function () use ($request, $bundling, $isiBundling) {

                $isBebas = $request->jenis_produk === Bundling::JENIS_BEBAS;

                $dataUpdate = [
                    'nama_bundling' => $request->nama_bundling,
                    'harga' => $request->harga,
                    'deskripsi' => $request->deskripsi,
                    'jenis_produk' => $request->jenis_produk,
                    'total_qty_khusus' => $isBebas ? (int) $request->total_qty_khusus : null,
                    'status_aktif' => $request->boolean('status_aktif', true),
                ];

                if ($request->hasFile('gambar')) {
                    $kodeBundling = $bundling->kode_bundling ?: ('BDL' . str_pad((string) $bundling->id, 4, '0', STR_PAD_LEFT));
                    Storage::disk('public')->deleteDirectory('bundling/' . $kodeBundling);
                    $dataUpdate['gambar'] = $request->file('gambar')->store('bundling/' . $kodeBundling, 'public');
                }

                $bundling->update($dataUpdate);

                $bundling->items()->delete();
                $this->simpanIsiBundling($bundling, $isiBundling);
            });

            return redirect()
                ->route('bundling.index')
                ->with('success', 'Bundling berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Bundling $bundling)
    {
        try {
            $kodeBundling = $bundling->kode_bundling ?: ('BDL' . str_pad((string) $bundling->id, 4, '0', STR_PAD_LEFT));
            if ($kodeBundling) {
                Storage::disk('public')->deleteDirectory('bundling/' . $kodeBundling);
            } elseif ($bundling->gambar && Storage::disk('public')->exists($bundling->gambar)) {
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