<?php

namespace App\Http\Controllers;

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
                    ->addColumn('nama_event_html', fn($item) => view('pages.promo-event.partials.nama', compact('item'))->render())
                    ->editColumn('produk.nama_produk', fn($item) => e(optional($item->produk)->nama_produk ?? '-'))
                    ->addColumn('isi_html', fn($item) => view('pages.promo-event.partials.isi', compact('item'))->render())
                    ->addColumn('harga_event_html', fn($item) => view('pages.promo-event.partials.harga', compact('item'))->render())
                    ->addColumn('periode_html', fn($item) => view('pages.promo-event.partials.periode', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.promo-event.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.promo-event.partials.aksi', compact('item'))->render())
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

        return view('pages.promo-event.index');
    }

    public function lihat($id)
    {
        try {
            $promoEvent = PromoProdukEvent::with(['produk', 'ukurans', 'varianRasas'])->findOrFail($id);
            return view('pages.promo-event.show', compact('promoEvent'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tambah()
    {
        try {
            $produks = Produk::with(['produkUkurans.ukuran', 'produkVarianRasas.varianRasa', 'produkVarians'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();
            return view('pages.promo-event.create', compact('produks'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function simpan(Request $request)
    {
        try {
            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id',
                'nama_event' => 'required|string|max:255',
                'isi_normal' => 'required|integer|min:1',
                'isi_event' => 'required|integer|min:1',
                'harga_event' => 'required|numeric|min:0',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'required|date|after_or_equal:mulai_berlaku',
                'status_aktif' => 'nullable|boolean',
                'ukuran_ids' => 'nullable|array',
                'ukuran_ids.*' => 'exists:ukurans,id',
                'varian_rasa_ids' => 'nullable|array',
                'varian_rasa_ids.*' => 'exists:varian_rasas,id',
            ]);

            $produk = Produk::with(['produkVarians', 'produkUkurans', 'produkVarianRasas'])->findOrFail($validated['produk_id']);
            $ukuranIds = $this->ukuranIdsValid($produk, $validated['ukuran_ids'] ?? []);
            $rasaIds = $this->rasaIdsValid($produk, $validated['varian_rasa_ids'] ?? []);

            $promoEvent = PromoProdukEvent::create([
                'produk_id' => $validated['produk_id'],
                'nama_event' => $validated['nama_event'],
                'isi_normal' => $validated['isi_normal'],
                'isi_event' => $validated['isi_event'],
                'harga_event' => $validated['harga_event'],
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'],
                'status_aktif' => $request->has('status_aktif') ? (bool) $request->status_aktif : true,
            ]);

            $promoEvent->ukurans()->sync($ukuranIds);
            $promoEvent->varianRasas()->sync($rasaIds);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promoEvent)
                ->withProperties([
                    'nama_event' => $promoEvent->nama_event,
                    'produk_id' => $promoEvent->produk_id,
                    'isi_normal' => $promoEvent->isi_normal,
                    'isi_event' => $promoEvent->isi_event,
                    'harga_event' => $promoEvent->harga_event,
                    'ukuran_ids' => $ukuranIds,
                    'varian_rasa_ids' => $rasaIds,
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
            $promoEvent = PromoProdukEvent::with(['ukurans', 'varianRasas', 'produk.produkVarians'])->findOrFail($id);
            $produks = Produk::with(['produkUkurans.ukuran', 'produkVarianRasas.varianRasa', 'produkVarians'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();
            $selectedUkurans = $promoEvent->ukurans->pluck('id')->toArray();
            $selectedRasas = $promoEvent->varianRasas->pluck('id')->toArray();

            // Jika produk memiliki varian di detail produk, filter selected agar hanya varian yang benar-benar ada di detail produk
            if ($promoEvent->produk && $promoEvent->produk->produkVarians->isNotEmpty()) {
                $validUkuranIds = $promoEvent->produk->produkVarians
                    ->filter(fn ($v) => !empty($v->ukuran))
                    ->map(fn ($v) => (int) ($v->ukuran_id ?: (\App\Models\Ukuran::where('nama_ukuran', $v->ukuran)->value('id') ?? 0)))
                    ->filter(fn ($vid) => $vid > 0)
                    ->unique()
                    ->values()
                    ->all();
                $selectedUkurans = array_values(array_intersect($selectedUkurans, $validUkuranIds));

                $validRasaIds = $promoEvent->produk->produkVarians
                    ->filter(fn ($v) => !empty($v->variasi))
                    ->map(fn ($v) => (int) ($v->varian_rasa_id ?: (\App\Models\VarianRasa::where('nama_rasa', $v->variasi)->value('id') ?? 0)))
                    ->filter(fn ($vid) => $vid > 0)
                    ->unique()
                    ->values()
                    ->all();
                $selectedRasas = array_values(array_intersect($selectedRasas, $validRasaIds));
            }

            return view('pages.promo-event.edit', compact('promoEvent', 'produks', 'selectedUkurans', 'selectedRasas'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function perbarui(Request $request, $id)
    {
        try {
            $promoEvent = PromoProdukEvent::findOrFail($id);

            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id',
                'nama_event' => 'required|string|max:255',
                'isi_normal' => 'required|integer|min:1',
                'isi_event' => 'required|integer|min:1',
                'harga_event' => 'required|numeric|min:0',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'required|date|after_or_equal:mulai_berlaku',
                'status_aktif' => 'nullable|boolean',
                'ukuran_ids' => 'nullable|array',
                'ukuran_ids.*' => 'exists:ukurans,id',
                'varian_rasa_ids' => 'nullable|array',
                'varian_rasa_ids.*' => 'exists:varian_rasas,id',
            ]);

            $produk = Produk::with(['produkVarians', 'produkUkurans', 'produkVarianRasas'])->findOrFail($validated['produk_id']);
            $ukuranIds = $this->ukuranIdsValid($produk, $validated['ukuran_ids'] ?? []);
            $rasaIds = $this->rasaIdsValid($produk, $validated['varian_rasa_ids'] ?? []);

            $promoEvent->update([
                'produk_id' => $validated['produk_id'],
                'nama_event' => $validated['nama_event'],
                'isi_normal' => $validated['isi_normal'],
                'isi_event' => $validated['isi_event'],
                'harga_event' => $validated['harga_event'],
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'],
                'status_aktif' => $request->has('status_aktif') ? (bool) $request->status_aktif : false,
            ]);

            $promoEvent->ukurans()->sync($ukuranIds);
            $promoEvent->varianRasas()->sync($rasaIds);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promoEvent)
                ->withProperties([
                    'nama_event' => $promoEvent->nama_event,
                    'produk_id' => $promoEvent->produk_id,
                    'isi_normal' => $promoEvent->isi_normal,
                    'isi_event' => $promoEvent->isi_event,
                    'harga_event' => $promoEvent->harga_event,
                    'ukuran_ids' => $ukuranIds,
                    'varian_rasa_ids' => $rasaIds,
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
     * Ukuran yang dibatasi hanya boleh milik produk yang dipilih.
     * Jika produk memiliki varian di detail produk (produk_varians), gunakan varian tersebut.
     */
    private function ukuranIdsValid(Produk $produk, array $ukuranIds): array
    {
        if (empty($ukuranIds)) {
            return [];
        }

        if ($produk->produkVarians()->exists()) {
            $varians = $produk->produkVarians()->get();
            $validUkuranIds = $varians->filter(fn ($v) => !empty($v->ukuran))
                ->map(fn ($v) => (int) ($v->ukuran_id ?: (\App\Models\Ukuran::where('nama_ukuran', $v->ukuran)->value('id') ?? 0)))
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            return collect($ukuranIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $validUkuranIds->contains($id))
                ->values()
                ->all();
        }

        if (!$produk->punya_ukuran) {
            return [];
        }

        return $produk->produkUkurans()
            ->whereIn('ukuran_id', $ukuranIds)
            ->pluck('ukuran_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Rasa/variasi yang dibatasi hanya boleh milik produk yang dipilih.
     * Jika produk memiliki varian di detail produk (produk_varians), gunakan varian tersebut.
     */
    private function rasaIdsValid(Produk $produk, array $rasaIds): array
    {
        if (empty($rasaIds)) {
            return [];
        }

        if ($produk->produkVarians()->exists()) {
            $varians = $produk->produkVarians()->get();
            $validRasaIds = $varians->filter(fn ($v) => !empty($v->variasi))
                ->map(fn ($v) => (int) ($v->varian_rasa_id ?: (\App\Models\VarianRasa::where('nama_rasa', $v->variasi)->value('id') ?? 0)))
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            return collect($rasaIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $validRasaIds->contains($id))
                ->values()
                ->all();
        }

        if (!$produk->punya_varian_rasa) {
            return [];
        }

        return $produk->produkVarianRasas()
            ->whereIn('varian_rasa_id', $rasaIds)
            ->pluck('varian_rasa_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}