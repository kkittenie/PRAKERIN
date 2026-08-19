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
        try {
            $search = $request->search;

            $promoEvent = PromoProdukEvent::with('produk')
                ->when($search, function ($query) use ($search) {
                    $query->where('nama_event', 'like', "%{$search}%");
                })
                ->latest()
                ->get();

            return view('pages.promo-event.index', compact('promoEvent', 'search'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
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