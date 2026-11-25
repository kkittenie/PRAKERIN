<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\PromoProdukEvent;
use App\Models\Produk;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class PromoProdukEventController extends Controller
{
    public function indeks(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = PromoProdukEvent::with('produk');

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nama_event_html', fn($item) => view('admin.promo-event.partials.nama', compact('item'))->render())
                    ->editColumn('produk.nama_produk', fn($item) => e(optional($item->produk)->nama_produk ?? '-'))
                    ->addColumn('isi_html', fn($item) => view('admin.promo-event.partials.isi', compact('item'))->render())
                    ->addColumn('harga_event_html', fn($item) => view('admin.promo-event.partials.harga', compact('item'))->render())
                    ->addColumn('periode_html', fn($item) => view('admin.promo-event.partials.periode', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('admin.promo-event.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('admin.promo-event.partials.aksi', compact('item'))->render())
                    ->rawColumns(['nama_event_html', 'isi_html', 'harga_event_html', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data promo produk event.'], 500);
            }
        }

        return view('admin.promo-event.index');
    }

    public function lihat($id)
    {
        try {
            $promoEvent = PromoProdukEvent::with(['produk.detailProduks', 'detailProduks'])->findOrFail($id);
            return view('admin.promo-event.show', compact('promoEvent'));
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
            $kategori = KategoriProduk::orderBy('nama_kategori')->get();
            return view('admin.promo-event.create', compact('produks', 'kategori'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function simpan(Request $request)
    {
        try {
            $validated = $request->validate([
                'produk_id'       => 'required|exists:produks,id',
                'kategori_id'     => 'required|exists:kategori_produks,id',
                'nama_event'      => 'required|string|max:255',
                'isi_event'       => 'required|integer|min:1',
                'harga_event'     => 'required|numeric|min:0',
                'mulai_berlaku'   => 'required|date',
                'selesai_berlaku' => 'required|date|after_or_equal:mulai_berlaku',
                'status_aktif'    => 'nullable|boolean',
                'nama_ukurans'    => 'nullable|array',
                'nama_ukurans.*'  => 'string|max:100',
                'nama_rasas'      => 'nullable|array',
                'nama_rasas.*'    => 'string|max:100',
            ], [
                'kategori_id.required' => 'Pilih kategori promo produk.',
                'kategori_id.exists'   => 'Kategori promo produk tidak valid.',
            ]);

            $produk    = Produk::with(['detailProduks', 'varianProduks'])->findOrFail($validated['produk_id']);
            $targetIds = $this->resolveDetailIds($produk, $request->input('nama_ukurans', []), $request->input('nama_rasas', []));

            $promoEvent = PromoProdukEvent::create([
                'produk_id'       => $validated['produk_id'],
                'kategori_id'     => $validated['kategori_id'],
                'nama_event'      => $validated['nama_event'],
                'isi_normal'      => (int) $validated['isi_event'],
                'isi_event'       => (int) $validated['isi_event'],
                'harga_event'     => $validated['harga_event'],
                'mulai_berlaku'   => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'],
                'status_aktif'    => $request->has('status_aktif') ? (bool) $request->status_aktif : true,
            ]);

            $promoEvent->detailProduks()->sync($targetIds['detail_ids']);
            $promoEvent->varianProduks()->sync($targetIds['varian_ids']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promoEvent)
                ->withProperties([
                    'nama_event'  => $promoEvent->nama_event,
                    'produk_id'   => $promoEvent->produk_id,
                    'harga_event' => $promoEvent->harga_event,
                    'detail_produk_ids' => $targetIds['detail_ids'],
                    'varian_produk_ids' => $targetIds['varian_ids'],
                ])
                ->log('menambahkan promo produk event');

            return redirect()
                ->route('promo-event.index')
                ->with('success', 'Promo Produk Event berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function ubah($id)
    {
        try {
            $promoEvent = PromoProdukEvent::with(['detailProduks', 'varianProduks', 'produk.detailProduks', 'produk.varianProduks'])->findOrFail($id);
            $produks    = Produk::with(['detailProduks', 'varianProduks'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();

            // Kirim nama string ke view: ukuran & variasi dari pilihan yang tersimpan
            if ($promoEvent->produk && $promoEvent->produk->is_global && $promoEvent->produk->jumlahVarianBersama() > 0) {
                $terpilih = $promoEvent->varianProduks->where('produk_id', $promoEvent->produk_id);
            } else {
                $terpilih = $promoEvent->detailProduks->where('produk_id', $promoEvent->produk_id);
            }

            $namaUnik = fn ($kolom) => $terpilih->pluck($kolom)
                ->map(fn ($n) => trim((string) $n))
                ->filter()
                ->unique(fn ($n) => mb_strtolower($n))
                ->values()
                ->all();
            $selectedNamaUkurans = $namaUnik('ukuran');
            $selectedNamaRasas   = $namaUnik('variasi');

            $kategori = KategoriProduk::orderBy('nama_kategori')->get();

            return view('admin.promo-event.edit', compact(
                'promoEvent', 'produks', 'kategori', 'selectedNamaUkurans', 'selectedNamaRasas'
            ));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function perbarui(Request $request, $id)
    {
        try {
            $promoEvent = PromoProdukEvent::findOrFail($id);

            $validated = $request->validate([
                'produk_id'       => 'required|exists:produks,id',
                'kategori_id'     => 'required|exists:kategori_produks,id',
                'nama_event'      => 'required|string|max:255',
                'isi_event'       => 'required|integer|min:1',
                'harga_event'     => 'required|numeric|min:0',
                'mulai_berlaku'   => 'required|date',
                'selesai_berlaku' => 'required|date|after_or_equal:mulai_berlaku',
                'status_aktif'    => 'nullable|boolean',
                'nama_ukurans'    => 'nullable|array',
                'nama_ukurans.*'  => 'string|max:100',
                'nama_rasas'      => 'nullable|array',
                'nama_rasas.*'    => 'string|max:100',
            ], [
                'kategori_id.required' => 'Pilih kategori promo produk.',
                'kategori_id.exists'   => 'Kategori promo produk tidak valid.',
            ]);

            $produk    = Produk::with(['detailProduks', 'varianProduks'])->findOrFail($validated['produk_id']);
            $targetIds = $this->resolveDetailIds($produk, $request->input('nama_ukurans', []), $request->input('nama_rasas', []));

            $promoEvent->update([
                'produk_id'       => $validated['produk_id'],
                'kategori_id'     => $validated['kategori_id'],
                'nama_event'      => $validated['nama_event'],
                'isi_normal'      => (int) $validated['isi_event'],
                'isi_event'       => (int) $validated['isi_event'],
                'harga_event'     => $validated['harga_event'],
                'mulai_berlaku'   => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'],
                'status_aktif'    => $request->has('status_aktif') ? (bool) $request->status_aktif : false,
            ]);

            $promoEvent->detailProduks()->sync($targetIds['detail_ids']);
            $promoEvent->varianProduks()->sync($targetIds['varian_ids']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promoEvent)
                ->withProperties([
                    'nama_event'  => $promoEvent->nama_event,
                    'produk_id'   => $promoEvent->produk_id,
                    'harga_event' => $promoEvent->harga_event,
                    'detail_produk_ids' => $targetIds['detail_ids'],
                    'varian_produk_ids' => $targetIds['varian_ids'],
                ])
                ->log('memperbarui promo produk event');

            return redirect()
                ->route('promo-event.index')
                ->with('success', 'Promo Produk Event berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $promoEvent = PromoProdukEvent::findOrFail($id);
            $promoEvent->status_aktif = !$promoEvent->status_aktif;
            $promoEvent->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promoEvent)
                ->withProperties([
                    'nama_event' => $promoEvent->nama_event,
                    'status_aktif' => $promoEvent->status_aktif,
                ])
                ->log('mengubah status promo produk event');

            return redirect()
                ->route('promo-event.index')
                ->with('success', 'Status "' . $promoEvent->nama_event . '"' . ($promoEvent->status_aktif ? ' berhasil diaktifkan.' : ' berhasil dinonaktifkan.'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapus($id)
    {
        try {
            $promoEvent = PromoProdukEvent::findOrFail($id);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promoEvent)
                ->withProperties([
                    'nama_event' => $promoEvent->nama_event,
                    'produk_id' => $promoEvent->produk_id,
                ])
                ->log('menghapus promo produk event');

            $promoEvent->delete();

            return redirect()
                ->route('promo-event.index')
                ->with('success', 'Promo Produk Event berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Ubah pilihan ukuran & rasa (nama) dari form jadi daftar ID detail produk dan varian produk.
     * Hasil berupa array: ['detail_ids' => [...], 'varian_ids' => [...]]
     */
    private function resolveDetailIds(Produk $produk, array $namaUkurans, array $namaRasas): array
    {
        $norm = fn ($x) => mb_strtolower(trim((string) $x));
        $u = collect($namaUkurans)->map($norm)->filter()->values();
        $r = collect($namaRasas)->map($norm)->filter()->values();

        if ($u->isEmpty() && $r->isEmpty()) {
            return [
                'detail_ids' => [],
                'varian_ids' => [],
            ];
        }

        // Jika Stok Bersama yang memiliki varian: cocokkan dengan varianProduks
        if ($produk->is_global && $produk->jumlahVarianBersama() > 0) {
            $varianIds = $produk->varianProduks
                ->filter(fn ($v) => ($u->isEmpty() || $u->contains($norm($v->ukuran)))
                    && ($r->isEmpty() || $r->contains($norm($v->variasi))))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            return [
                'detail_ids' => [],
                'varian_ids' => $varianIds,
            ];
        }

        // Stok Per Varian: cocokkan dengan detailProduks
        $detailIds = $produk->detailProduks
            ->filter(fn ($d) => ($u->isEmpty() || $u->contains($norm($d->ukuran)))
                && ($r->isEmpty() || $r->contains($norm($d->variasi))))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return [
            'detail_ids' => $detailIds,
            'varian_ids' => [],
        ];
    }
}