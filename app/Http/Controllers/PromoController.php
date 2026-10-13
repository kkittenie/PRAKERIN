<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Models\Produk;
use App\Models\PromoProdukUkuran;
use App\Models\PromoProdukRasa;
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
                    ->addColumn('nama_promo_html', fn($item) => view('pages.promo.partials.nama', compact('item'))->render())
                    ->addColumn('diskon_html', fn($item) => view('pages.promo.partials.diskon', compact('item'))->render())
                    ->addColumn('min_pembelian_html', fn($item) => view('pages.promo.partials.min_pembelian', compact('item'))->render())
                    ->addColumn('mulai_berlaku_html', fn($item) => view('pages.promo.partials.mulai_berlaku', compact('item'))->render())
                    ->addColumn('selesai_berlaku_html', fn($item) => view('pages.promo.partials.selesai_berlaku', compact('item'))->render())
                    ->addColumn('jumlah_produk_html', fn($item) => view('pages.promo.partials.jumlah_produk', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.promo.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.promo.partials.aksi', compact('item'))->render())
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

        return view('pages.promo.index');
    }

    public function lihat($id)
    {
        try {
            $promo = Promo::with(['produks.kategori', 'produkUkuranPivots'])->findOrFail($id);
            return view('pages.promo.show', compact('promo'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tambah()
    {
        try {
            $produks = Produk::with(['produkVarians', 'produkUkurans', 'produkVarianRasas.varianRasa'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();
            $produkData = $this->dataProdukUntukForm($produks);
            return view('pages.promo.create', compact('produks', 'produkData'));
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
                // Nama di-resolve ke ID master waktu disimpan.
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
            $ukuranIdsPerProduk = $this->resolveIdsPerProduk($validated['produk_ids'], $validated['ukuran_names'] ?? [], 'ukuran');
            $rasaIdsPerProduk = $this->resolveIdsPerProduk($validated['produk_ids'], $validated['rasa_names'] ?? [], 'rasa');
            $this->simpanPembatasanUkuranPerProduk($promo, $validated['produk_ids'], $ukuranIdsPerProduk);
            $this->simpanPembatasanRasaPerProduk($promo, $validated['produk_ids'], $rasaIdsPerProduk);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'jenis_potongan' => $promo->jenis_potongan,
                    'potongan' => $promo->jenis_potongan === 'nominal' ? $promo->nominal_potongan : $promo->persentase,
                    'produks' => $validated['produk_ids'],
                    'ukuran_ids' => $ukuranIdsPerProduk,
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
     * Pilihan diidentifikasi lewat NAMA (bukan ID), karena baris produk_varians
     * yang dibuat dari form Produk cuma nyimpen teks ukuran/variasi tanpa
     * ukuran_id / varian_rasa_id. Nama di-resolve ke ID master pas disimpan.
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
            if ($p->produkVarians && $p->produkVarians->isNotEmpty()) {
                $ukurans = $unikNama($p->produkVarians->pluck('ukuran'));
                $rasas = $unikNama($p->produkVarians->pluck('variasi'));
            } else {
                $ukurans = $p->punya_ukuran
                    ? $unikNama($p->produkUkurans->map(fn ($pu) => optional($pu->ukuran)->nama_ukuran))
                    : collect();
                $rasas = $p->punya_varian_rasa
                    ? $unikNama(collect($p->daftar_varian_rasa)->pluck('nama'))
                    : collect();
            }

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
     * Ubah nama ukuran/rasa yang dikirim form jadi ID master, per produk.
     * $namaPerProduk: [produk_id => ['Kecil', 'Besar', ...]]
     * Hasil: [produk_id => [ukuran_id, ...]] (atau varian_rasa_id kalau $jenis = 'rasa').
     * Logikanya sama dengan resolveUkuranIds/resolveRasaIds di Promo Event:
     * cari di produk_varians dulu, lalu master; kalau belum ada, dibuat.
     */
    private function resolveIdsPerProduk(array $produkIds, array $namaPerProduk, string $jenis): array
    {
        if (empty($namaPerProduk)) {
            return [];
        }

        $produkIds = array_map('intval', $produkIds);
        $produks = Produk::with('produkVarians')->whereIn('id', $produkIds)->get()->keyBy('id');

        $kolomNama = $jenis === 'ukuran' ? 'ukuran' : 'variasi';
        $kolomId = $jenis === 'ukuran' ? 'ukuran_id' : 'varian_rasa_id';

        $hasil = [];
        foreach ($namaPerProduk as $produkId => $namaList) {
            $produkId = (int) $produkId;
            $produk = $produks->get($produkId);
            if (!$produk || !is_array($namaList)) {
                continue;
            }

            $ids = [];
            foreach ($namaList as $nama) {
                $nama = trim((string) $nama);
                if ($nama === '') {
                    continue;
                }

                $varian = $produk->produkVarians->first(
                    fn ($v) => strcasecmp((string) ($v->{$kolomNama} ?? ''), $nama) === 0 && !empty($v->{$kolomId})
                );

                if ($varian) {
                    $ids[] = (int) $varian->{$kolomId};
                    continue;
                }

                if ($jenis === 'ukuran') {
                    $master = \App\Models\Ukuran::firstOrCreate(
                        ['nama_ukuran' => $nama],
                        ['urutan' => 99, 'status_aktif' => true]
                    );
                } else {
                    $master = \App\Models\VarianRasa::firstOrCreate(
                        ['nama_rasa' => $nama],
                        ['status_aktif' => true]
                    );
                }
                $ids[] = (int) $master->id;
            }

            if (!empty($ids)) {
                $hasil[$produkId] = array_values(array_unique($ids));
            }
        }

        return $hasil;
    }

    /**
     * Simpan ulang pembatasan ukuran per-produk buat promo ini.
     * $ukuranIdsPerProduk formatnya: [produk_id => [ukuran_id, ...], ...]
     * (persis bentuk input ukuran_ids[<produk_id>][] dari form).
     * Produk yang gak ada di $produkIds (misal udah di-uncheck) otomatis
     * ikut kehapus pembatasannya juga.
     */
    private function simpanPembatasanUkuranPerProduk(Promo $promo, array $produkIds, array $ukuranIdsPerProduk): void
    {
        $promo->produkUkuranPivots()->delete();

        // Normalisasi ke int dulu — $produkIds dari request masih berupa
        // string ("5", "12", dst), jadi kalau dibandingin strict (in_array
        // ...true) sama $produkId yang udah di-(int)-in, selalu dianggap
        // gak ketemu dan pembatasan ukurannya gak pernah kesimpen.
        $produkIds = array_map('intval', $produkIds);

        $rows = [];
        $now = now();
        foreach ($ukuranIdsPerProduk as $produkId => $ukuranIds) {
            $produkId = (int) $produkId;
            if (!in_array($produkId, $produkIds, true) || empty($ukuranIds)) {
                continue;
            }
            foreach (array_unique($ukuranIds) as $ukuranId) {
                $rows[] = [
                    'promo_id' => $promo->id,
                    'produk_id' => $produkId,
                    'ukuran_id' => (int) $ukuranId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($rows)) {
            PromoProdukUkuran::insert($rows);
        }
    }

    /**
     * Simpan ulang pembatasan rasa per-produk buat promo ini.
     * $rasaIdsPerProduk formatnya: [produk_id => [varian_rasa_id, ...], ...]
     */
    private function simpanPembatasanRasaPerProduk(Promo $promo, array $produkIds, array $rasaIdsPerProduk): void
    {
        $promo->produkRasaPivots()->delete();

        $produkIds = array_map('intval', $produkIds);

        $rows = [];
        $now = now();
        foreach ($rasaIdsPerProduk as $produkId => $rasaIds) {
            $produkId = (int) $produkId;
            if (!in_array($produkId, $produkIds, true) || empty($rasaIds)) {
                continue;
            }
            foreach (array_unique($rasaIds) as $rasaId) {
                $rows[] = [
                    'promo_id' => $promo->id,
                    'produk_id' => $produkId,
                    'varian_rasa_id' => (int) $rasaId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($rows)) {
            PromoProdukRasa::insert($rows);
        }
    }

    public function ubah($id)
    {
        try {
            $promo = Promo::with(['produks', 'produkUkuranPivots', 'produkRasaPivots'])->findOrFail($id);
            $produks = Produk::with(['produkVarians', 'produkUkurans', 'produkVarianRasas.varianRasa'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();
            $produkData = $this->dataProdukUntukForm($produks);
            $selectedProduks = $promo->produks->pluck('id')->toArray();
            // [produk_id => [nama ukuran, ...]] — pembatasan yang udah kesimpen per produk.
            // Nama diambil dari master & disamakan huruf besar/kecilnya dengan nama di data produk,
            // karena form ngidentifikasi pilihan lewat nama.
            $produkMap = collect($produkData)->keyBy('id');
            $samakanNama = function ($produkId, $nama, $kunci) use ($produkMap) {
                $daftar = collect(($produkMap->get($produkId) ?? [])[$kunci] ?? []);
                $cocok = $daftar->first(fn ($x) => strcasecmp($x['nama'], $nama) === 0);
                return $cocok['nama'] ?? $nama;
            };
            $selectedUkuranPerProduk = $promo->produkUkuranPivots->load('ukuran')
                ->groupBy('produk_id')
                ->map(fn ($rows, $pid) => $rows->map(fn ($r) => optional($r->ukuran)->nama_ukuran)->filter()
                    ->map(fn ($n) => $samakanNama((int) $pid, $n, 'ukurans'))->values());
            // [produk_id => [nama rasa, ...]] — pembatasan rasa per produk
            $selectedRasaPerProduk = $promo->produkRasaPivots->load('varianRasa')
                ->groupBy('produk_id')
                ->map(fn ($rows, $pid) => $rows->map(fn ($r) => optional($r->varianRasa)->nama_rasa)->filter()
                    ->map(fn ($n) => $samakanNama((int) $pid, $n, 'rasas'))->values());

            return view('pages.promo.edit', compact('promo', 'produks', 'produkData', 'selectedProduks', 'selectedUkuranPerProduk', 'selectedRasaPerProduk'));
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
            $ukuranIdsPerProduk = $this->resolveIdsPerProduk($validated['produk_ids'], $validated['ukuran_names'] ?? [], 'ukuran');
            $rasaIdsPerProduk = $this->resolveIdsPerProduk($validated['produk_ids'], $validated['rasa_names'] ?? [], 'rasa');
            $this->simpanPembatasanUkuranPerProduk($promo, $validated['produk_ids'], $ukuranIdsPerProduk);
            $this->simpanPembatasanRasaPerProduk($promo, $validated['produk_ids'], $rasaIdsPerProduk);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'jenis_potongan' => $promo->jenis_potongan,
                    'potongan' => $promo->jenis_potongan === 'nominal' ? $promo->nominal_potongan : $promo->persentase,
                    'produks' => $validated['produk_ids'],
                    'ukuran_ids' => $ukuranIdsPerProduk,
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