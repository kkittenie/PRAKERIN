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
                    ->addColumn('nama_event_html', function ($item) {
                        return '<a href="' . route('promo-event.show', $item->id) . '" class="hover:text-amber-600 dark:hover:text-amber-400 transition font-semibold text-gray-900 dark:text-white">' . e($item->nama_event) . '</a>';
                    })
                    ->editColumn('produk.nama_produk', function ($item) {
                        return e(optional($item->produk)->nama_produk ?? '-');
                    })
                    ->addColumn('isi_html', function ($item) {
                        return $item->isi_normal . ' pcs &rarr; ' . $item->isi_event . ' pcs';
                    })
                    ->addColumn('harga_event_html', function ($item) {
                        return 'Rp ' . number_format($item->harga_event, 0, ',', '.');
                    })
                    ->addColumn('periode_html', function ($item) {
                        return $item->mulai_berlaku->format('d M Y H:i') . ' - ' . $item->selesai_berlaku->format('d M Y H:i');
                    })
                    ->addColumn('status_html', function ($item) {
                        $now = now();
                        $isKadaluwarsa = $item->selesai_berlaku && $now > $item->selesai_berlaku;
                        $selesaiIso = $item->selesai_berlaku ? $item->selesai_berlaku->toIso8601String() : '';

                        $badgeExpired = '<span class="promo-event-expired-badge items-center rounded-full bg-red-100 dark:bg-red-500/10 px-3 py-1 text-xs font-medium text-red-700 dark:text-red-400 ' . ($isKadaluwarsa ? 'inline-flex' : 'hidden') . '">Kadaluwarsa</span>';

                        $checked = $item->status_aktif ? 'checked' : '';
                        $csrf = csrf_field();
                        $method = method_field('PATCH');
                        $url = route('promo-event.toggle-status', $item->id);

                        $toggleForm = '<form action="' . $url . '" method="POST" class="promo-event-toggle-form ' . ($isKadaluwarsa ? 'hidden' : 'inline') . '">' . $csrf . $method .
                                      '<label class="relative inline-flex items-center cursor-pointer" title="Klik untuk mengaktifkan/menonaktifkan">' .
                                      '<input type="checkbox" class="sr-only peer" onchange="this.form.submit()" ' . $checked . '>' .
                                      '<div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[\'\'] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>' .
                                      '</label></form>';

                        return '<div data-promo-event-status-cell data-selesai="' . $selesaiIso . '">' . $badgeExpired . $toggleForm . '</div>';
                    })
                    ->addColumn('aksi', function ($item) {
                        $showUrl = route('promo-event.show', $item->id);
                        $editUrl = route('promo-event.edit', $item->id);
                        $destroyUrl = route('promo-event.destroy', $item->id);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $namaEscaped = e(addslashes($item->nama_event));

                        return '<div class="inline-flex items-center gap-0.5 justify-end">' .
                               '<a href="' . $showUrl . '" title="Detail" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>' .
                               '<a href="' . $editUrl . '" title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>' .
                               '<form action="' . $destroyUrl . '" method="POST" class="inline">' . $csrf . $method .
                               '<button type="button" title="Hapus" @click="bukaModalHapus($el.closest(\'form\'), \'' . $namaEscaped . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>' .
                               '</form></div>';
                    })
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
            $promoEvent = PromoProdukEvent::with('produk')->findOrFail($id);
            return view('pages.promo-event.show', compact('promoEvent'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tambah()
    {
        try {
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();
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
            ]);

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

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promoEvent)
                ->withProperties([
                    'nama_event' => $promoEvent->nama_event,
                    'produk_id' => $promoEvent->produk_id,
                    'isi_normal' => $promoEvent->isi_normal,
                    'isi_event' => $promoEvent->isi_event,
                    'harga_event' => $promoEvent->harga_event,
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
            $promoEvent = PromoProdukEvent::findOrFail($id);
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();

            return view('pages.promo-event.edit', compact('promoEvent', 'produks'));
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
            ]);

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

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promoEvent)
                ->withProperties([
                    'nama_event' => $promoEvent->nama_event,
                    'produk_id' => $promoEvent->produk_id,
                    'isi_normal' => $promoEvent->isi_normal,
                    'isi_event' => $promoEvent->isi_event,
                    'harga_event' => $promoEvent->harga_event,
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
}