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
                    ->addColumn('nama_promo_html', function ($item) {
                        return '<a href="' . route('promo.show', $item->id) . '" class="hover:text-amber-600 dark:hover:text-amber-400 transition font-semibold text-gray-900 dark:text-white">' . e($item->nama_promo) . '</a>';
                    })
                    ->addColumn('diskon_html', function ($item) {
                        if ($item->jenis_potongan === 'nominal') {
                            return 'Rp ' . number_format($item->nominal_potongan, 0, ',', '.');
                        }
                        $persentaseFormatted = rtrim(rtrim(number_format($item->persentase, 2, ',', '.'), '0'), ',');
                        return $persentaseFormatted . '%';
                    })
                    ->addColumn('min_pembelian_html', function ($item) {
                        return $item->min_pembelian > 0 ? 'Rp ' . number_format($item->min_pembelian, 0, ',', '.') : '-';
                    })
                    ->addColumn('mulai_berlaku_html', function ($item) {
                        return $item->mulai_berlaku ? $item->mulai_berlaku->format('d M Y H:i') : '-';
                    })
                    ->addColumn('selesai_berlaku_html', function ($item) {
                        if ($item->selesai_berlaku) {
                            return $item->selesai_berlaku->format('d M Y H:i');
                        }
                        return '<span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium">Selamanya</span>';
                    })
                    ->addColumn('jumlah_produk_html', function ($item) {
                        return '<span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/10 px-3 py-1 text-xs font-medium text-amber-800 dark:text-amber-400">' . $item->produks_count . ' Produk</span>';
                    })
                    ->addColumn('status_html', function ($item) {
                        $now = now();
                        $isKadaluwarsa = $item->selesai_berlaku && $now > $item->selesai_berlaku;
                        $selesaiIso = $item->selesai_berlaku ? $item->selesai_berlaku->toIso8601String() : '';

                        $badgeExpired = '<span class="promo-expired-badge items-center rounded-full bg-red-100 dark:bg-red-500/10 px-3 py-1 text-xs font-medium text-red-700 dark:text-red-400 ' . ($isKadaluwarsa ? 'inline-flex' : 'hidden') . '">Kadaluwarsa</span>';

                        $checked = $item->status_aktif ? 'checked' : '';
                        $csrf = csrf_field();
                        $method = method_field('PATCH');
                        $url = route('promo.toggle-status', $item->id);

                        $toggleForm = '<form action="' . $url . '" method="POST" class="promo-toggle-form ' . ($isKadaluwarsa ? 'hidden' : 'inline') . '">' . $csrf . $method .
                                      '<label class="relative inline-flex items-center cursor-pointer" title="Klik untuk mengaktifkan/menonaktifkan promo">' .
                                      '<input type="checkbox" class="sr-only peer" onchange="this.form.submit()" ' . $checked . '>' .
                                      '<div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[\'\'] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>' .
                                      '</label></form>';

                        return '<div data-promo-status-cell data-selesai="' . $selesaiIso . '">' . $badgeExpired . $toggleForm . '</div>';
                    })
                    ->addColumn('aksi', function ($item) {
                        $showUrl = route('promo.show', $item->id);
                        $editUrl = route('promo.edit', $item->id);
                        $destroyUrl = route('promo.destroy', $item->id);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $namaEscaped = e(addslashes($item->nama_promo));

                        return '<div class="inline-flex items-center gap-0.5 justify-end">' .
                               '<a href="' . $showUrl . '" title="Detail" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>' .
                               '<a href="' . $editUrl . '" title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>' .
                               '<form action="' . $destroyUrl . '" method="POST" class="inline">' . $csrf . $method .
                               '<button type="button" title="Hapus" @click="bukaModalHapus($el.closest(\'form\'), \'' . $namaEscaped . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>' .
                               '</form></div>';
                    })
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
            $promo = Promo::with(['produks.kategori'])->findOrFail($id);
            return view('pages.promo.show', compact('promo'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tambah()
    {
        try {
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();
            return view('pages.promo.create', compact('produks'));
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

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'jenis_potongan' => $promo->jenis_potongan,
                    'potongan' => $promo->jenis_potongan === 'nominal' ? $promo->nominal_potongan : $promo->persentase,
                    'produks' => $validated['produk_ids'],
                ])
                ->log('menambahkan promo');

            return redirect()
                ->route('promo.index')
                ->with('success', 'Promo berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function ubah($id)
    {
        try {
            $promo = Promo::with('produks')->findOrFail($id);
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();
            $selectedProduks = $promo->produks->pluck('id')->toArray();

            return view('pages.promo.edit', compact('promo', 'produks', 'selectedProduks'));
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

            activity()
                ->causedBy(auth()->user())
                ->performedOn($promo)
                ->withProperties([
                    'promo' => $promo->nama_promo,
                    'jenis_potongan' => $promo->jenis_potongan,
                    'potongan' => $promo->jenis_potongan === 'nominal' ? $promo->nominal_potongan : $promo->persentase,
                    'produks' => $validated['produk_ids'],
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