<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Models\Produk;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromoController extends Controller
{
    public function indeks(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Promo::withCount('produks');

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nama_promo_html', fn($item) => view('admin.promo.partials.nama', compact('item'))->render())
                    ->addColumn('diskon_html', fn($item) => view('admin.promo.partials.diskon', compact('item'))->render())
                    ->addColumn('min_pembelian_html', fn($item) => view('admin.promo.partials.min_pembelian', compact('item'))->render())
                    ->addColumn('mulai_berlaku_html', fn($item) => view('admin.promo.partials.mulai_berlaku', compact('item'))->render())
                    ->addColumn('selesai_berlaku_html', fn($item) => view('admin.promo.partials.selesai_berlaku', compact('item'))->render())
                    ->addColumn('jumlah_produk_html', fn($item) => view('admin.promo.partials.jumlah_produk', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('admin.promo.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('admin.promo.partials.aksi', compact('item'))->render())
                    ->rawColumns(['nama_promo_html', 'diskon_html', 'selesai_berlaku_html', 'jumlah_produk_html', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data promo.'], 500);
            }
        }

        return view('admin.promo.index');
    }

    public function lihat($id)
    {
        try {
            $promo = Promo::with(['produks.kategori', 'detailProduks'])->findOrFail($id);
            return view('admin.promo.show', compact('promo'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tambah()
    {
        try {
            $produks = Produk::with(['detailProduks', 'varianProduks'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();
            $produkData = $this->dataProdukUntukForm($produks);
            return view('admin.promo.create', compact('produks', 'produkData'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function simpan(Request $request)
    {
        try {
            $this->normalisasiInputKoma($request);

            $validated = $request->validate([
                'nama_promo' => 'required|string|max:255',
                'jenis_potongan' => 'required|in:persentase,nominal',
                'persentase' => 'nullable|required_if:jenis_potongan,persentase|numeric|min:0|max:100',
                'nominal_potongan' => 'nullable|required_if:jenis_potongan,nominal|numeric|min:0',
                'min_pembelian' => 'nullable|numeric|min:0',
                'status_aktif' => 'nullable|boolean',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'nullable|date|after_or_equal:mulai_berlaku',
                'produk_ids' => 'required|array',
                'produk_ids.*' => 'exists:produks,id',
                // Dikirim per-produk berupa NAMA (sama kayak form Promo Event):
                // ukuran_names[<produk_id>][] = 'Kecil', rasa_names[<produk_id>][] = 'Vanilla'.
                // Nama dicocokkan ke detail produk waktu disimpan.
                'ukuran_names' => 'nullable|array',
                'ukuran_names.*' => 'nullable|array',
                'ukuran_names.*.*' => 'string|max:100',
                'rasa_names' => 'nullable|array',
                'rasa_names.*' => 'nullable|array',
                'rasa_names.*.*' => 'string|max:100',
            ]);

            $promo = Promo::create([
                'nama_promo' => $validated['nama_promo'],
                'jenis_potongan' => $validated['jenis_potongan'],
                'persentase' => $validated['jenis_potongan'] === 'persentase' ? ($validated['persentase'] ?? 0) : 0,
                'nominal_potongan' => $validated['jenis_potongan'] === 'nominal' ? ($validated['nominal_potongan'] ?? 0) : 0,
                'min_pembelian' => $validated['min_pembelian'] ?? 0,
                'status_aktif' => $request->has('status_aktif') ? (bool)$request->status_aktif : true,
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'] ?? null,
            ]);

            $promo->produks()->sync($validated['produk_ids']);
            $detailIdsPerProduk = $this->resolveDetailIdsPerProduk($validated['produk_ids'], $validated['ukuran_names'] ?? [], $validated['rasa_names'] ?? []);
            $this->simpanTargetDetail($promo, $detailIdsPerProduk);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'jenis_potongan' => $promo->jenis_potongan,
                    'potongan' => $promo->jenis_potongan === 'nominal' ? $promo->nominal_potongan : $promo->persentase,
                    'produks' => $validated['produk_ids'],
                    'detail_produk_ids' => $detailIdsPerProduk,
                ])
                ->log('menambahkan promo');

            return redirect()
                ->route('promo.index')
                ->with('success', 'Promo berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Data produk buat form promo (create/edit): id, nama, foto, harga, plus
     * daftar ukuran & rasa tiap produk — dasarnya sama kayak form Promo Event.
     * Pilihan di form diidentifikasi lewat NAMA; waktu disimpan, nama itu
     * dicocokkan ke baris detail_produks (lihat resolveDetailIdsPerProduk).
     */
    private function dataProdukUntukForm($produks)
    {
        $unikNama = fn ($daftar) => $daftar
            ->map(fn ($nama) => trim((string) $nama))
            ->filter()
            ->unique(fn ($nama) => mb_strtolower($nama))
            ->map(fn ($nama) => ['nama' => $nama])
            ->values();

        return $produks->map(function ($p) use ($unikNama) {
            if ($p->is_global && $p->jumlahVarianBersama() > 0) {
                $detail = $p->varianBersamaAktifTersedia();
            } else {
                $detail = $p->daftarDetail();
            }
            $ukurans = $unikNama($detail->pluck('ukuran'));
            $rasas = $unikNama($detail->pluck('variasi'));

            return [
                'id' => $p->id,
                'nama_produk' => $p->nama_produk,
                'kode_produk' => $p->kode_produk,
                'foto' => $p->foto,
                'harga_jual' => (float) $p->harga_jual,
                'punya_ukuran' => $ukurans->isNotEmpty(),
                'ukurans' => $ukurans,
                'punya_varian_rasa' => $rasas->isNotEmpty(),
                'rasas' => $rasas,
            ];
        })->values();
    }

    /**
     * Ubah pilihan ukuran/rasa (nama) dari form jadi daftar detail & varian produk, per produk.
     * Hasil: ['detail_ids' => [produk_id => [detail_id, ...]], 'varian_ids' => [produk_id => [varian_id, ...]]]
     */
    private function resolveDetailIdsPerProduk(array $produkIds, array $ukuranPerProduk, array $rasaPerProduk): array
    {
        $produkIds = array_map('intval', $produkIds);
        $produks = Produk::with(['detailProduks', 'varianProduks'])->whereIn('id', $produkIds)->get()->keyBy('id');

        $norm = fn ($x) => mb_strtolower(trim((string) $x));
        $daftarNama = fn ($daftar) => collect(is_array($daftar) ? $daftar : [])
            ->map($norm)
            ->filter()
            ->values();

        $detailHasil = [];
        $varianHasil = [];
        foreach ($produkIds as $produkId) {
            $produk = $produks->get($produkId);
            if (!$produk) {
                continue;
            }

            $u = $daftarNama($ukuranPerProduk[$produkId] ?? []);
            $r = $daftarNama($rasaPerProduk[$produkId] ?? []);
            if ($u->isEmpty() && $r->isEmpty()) {
                continue;
            }

            if ($produk->is_global && $produk->jumlahVarianBersama() > 0) {
                $vIds = $produk->varianProduks
                    ->filter(fn ($v) => ($u->isEmpty() || $u->contains($norm($v->ukuran)))
                        && ($r->isEmpty() || $r->contains($norm($v->variasi))))
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                if (!empty($vIds)) {
                    $varianHasil[$produkId] = $vIds;
                }
            } else {
                $dIds = $produk->detailProduks
                    ->filter(fn ($d) => ($u->isEmpty() || $u->contains($norm($d->ukuran)))
                        && ($r->isEmpty() || $r->contains($norm($d->variasi))))
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                if (!empty($dIds)) {
                    $detailHasil[$produkId] = $dIds;
                }
            }
        }

        return [
            'detail_ids' => $detailHasil,
            'varian_ids' => $varianHasil,
        ];
    }

    /**
     * Simpan ulang target detail produk dan varian produk buat promo ini.
     */
    private function simpanTargetDetail(Promo $promo, array $target): void
    {
        $promo->detailProduks()->sync(
            collect($target['detail_ids'] ?? [])->flatten()->map(fn ($id) => (int) $id)->unique()->values()->all()
        );
        $promo->varianProduks()->sync(
            collect($target['varian_ids'] ?? [])->flatten()->map(fn ($id) => (int) $id)->unique()->values()->all()
        );
    }

    public function ubah($id)
    {
        try {
            $promo = Promo::with(['produks', 'detailProduks', 'varianProduks'])->findOrFail($id);
            $produks = Produk::with(['detailProduks', 'varianProduks'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();
            $produkData = $this->dataProdukUntukForm($produks);
            $selectedProduks = $promo->produks->pluck('id')->toArray();

            $produkMap = collect($produkData)->keyBy('id');
            $samakanNama = function ($produkId, $nama, $kunci) use ($produkMap) {
                $daftar = collect(($produkMap->get($produkId) ?? [])[$kunci] ?? []);
                $cocok = $daftar->first(fn ($x) => strcasecmp($x['nama'], $nama) === 0);
                return $cocok['nama'] ?? $nama;
            };

            $gabunganPilihan = $promo->detailProduks->map(fn ($d) => (object) [
                'produk_id' => $d->produk_id,
                'ukuran' => $d->ukuran,
                'variasi' => $d->variasi,
            ])->concat($promo->varianProduks->map(fn ($v) => (object) [
                'produk_id' => $v->produk_id,
                'ukuran' => $v->ukuran,
                'variasi' => $v->variasi,
            ]));

            $namaPerProduk = fn ($kolom, $kunci) => $gabunganPilihan
                ->groupBy('produk_id')
                ->map(fn ($rows, $pid) => $rows->pluck($kolom)
                    ->map(fn ($n) => trim((string) $n))
                    ->filter()
                    ->unique(fn ($n) => mb_strtolower($n))
                    ->map(fn ($n) => $samakanNama((int) $pid, $n, $kunci))
                    ->values());
            $selectedUkuranPerProduk = $namaPerProduk('ukuran', 'ukurans');
            $selectedRasaPerProduk = $namaPerProduk('variasi', 'rasas');

            return view('admin.promo.edit', compact('promo', 'produks', 'produkData', 'selectedProduks', 'selectedUkuranPerProduk', 'selectedRasaPerProduk'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function perbarui(Request $request, $id)
    {
        try {
            $promo = Promo::findOrFail($id);

            $this->normalisasiInputKoma($request);

            $validated = $request->validate([
                'nama_promo' => 'required|string|max:255',
                'jenis_potongan' => 'required|in:persentase,nominal',
                'persentase' => 'nullable|required_if:jenis_potongan,persentase|numeric|min:0|max:100',
                'nominal_potongan' => 'nullable|required_if:jenis_potongan,nominal|numeric|min:0',
                'min_pembelian' => 'nullable|numeric|min:0',
                'status_aktif' => 'nullable|boolean',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'nullable|date|after_or_equal:mulai_berlaku',
                'produk_ids' => 'required|array',
                'produk_ids.*' => 'exists:produks,id',
                'ukuran_names' => 'nullable|array',
                'ukuran_names.*' => 'nullable|array',
                'ukuran_names.*.*' => 'string|max:100',
                'rasa_names' => 'nullable|array',
                'rasa_names.*' => 'nullable|array',
                'rasa_names.*.*' => 'string|max:100',
            ]);

            $promo->update([
                'nama_promo' => $validated['nama_promo'],
                'jenis_potongan' => $validated['jenis_potongan'],
                'persentase' => $validated['jenis_potongan'] === 'persentase' ? ($validated['persentase'] ?? 0) : 0,
                'nominal_potongan' => $validated['jenis_potongan'] === 'nominal' ? ($validated['nominal_potongan'] ?? 0) : 0,
                'min_pembelian' => $validated['min_pembelian'] ?? 0,
                'status_aktif' => $request->has('status_aktif') ? (bool)$request->status_aktif : false,
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'] ?? null,
            ]);

            $promo->produks()->sync($validated['produk_ids']);
            $detailIdsPerProduk = $this->resolveDetailIdsPerProduk($validated['produk_ids'], $validated['ukuran_names'] ?? [], $validated['rasa_names'] ?? []);
            $this->simpanTargetDetail($promo, $detailIdsPerProduk);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'jenis_potongan' => $promo->jenis_potongan,
                    'potongan' => $promo->jenis_potongan === 'nominal' ? $promo->nominal_potongan : $promo->persentase,
                    'produks' => $validated['produk_ids'],
                    'detail_produk_ids' => $detailIdsPerProduk,
                ])
                ->log('memperbarui promo');

            return redirect()
                ->route('promo.index')
                ->with('success', 'Promo berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $promo = Promo::findOrFail($id);
            $promo->status_aktif = !$promo->status_aktif;
            $promo->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'status_aktif' => $promo->status_aktif,
                ])
                ->log('mengubah status promo');

            return redirect()
                ->route('promo.index')
                ->with('success', 'Status promo "' . $promo->nama_promo . '"' . ($promo->status_aktif ? ' berhasil diaktifkan.' : ' berhasil dinonaktifkan.'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapus($id)
    {
        try {
            $promo = Promo::findOrFail($id);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'persentase' => $promo->persentase,
                    'produks' => $promo->produks->pluck('id')->toArray(),
                ])
                ->log('menghapus promo');

            $promo->produks()->detach();
            $promo->delete();

            return redirect()
                ->route('promo.index')
                ->with('success', 'Promo berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Normalisasi input koma
     */
    private function normalisasiInputKoma(Request $request): void
    {
        foreach (['persentase', 'nominal_potongan', 'min_pembelian'] as $field) {
            if ($request->filled($field) && is_string($request->input($field))) {
                $request->merge([
                    $field => str_replace(',', '.', $request->input($field)),
                ]);
            }
        }
    }
}