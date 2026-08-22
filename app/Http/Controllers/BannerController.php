<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    private const GAMBAR_PATH = 'banner/images';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Banner::orderBy('urutan')->latest();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('gambar_html', function ($item) {
                        return '<img src="' . asset('storage/' . $item->gambar) . '" class="w-16 h-10 rounded-lg object-cover border mx-auto">';
                    })
                    ->editColumn('judul', function ($item) {
                        return '<span class="font-semibold text-gray-900 dark:text-white">' . e($item->judul) . '</span>';
                    })
                    ->addColumn('urutan_html', function ($item) {
                        return '<span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/10 px-3 py-1 text-sm font-medium text-amber-800 dark:text-amber-400">' . $item->urutan . '</span>';
                    })
                    ->addColumn('status_html', function ($item) {
                        $checked = $item->status_aktif ? 'checked' : '';
                        $csrf = csrf_field();
                        $method = method_field('PATCH');
                        $url = route('banner.toggle-status', $item->id);

                        return '<form action="' . $url . '" method="POST" class="inline">' . $csrf . $method .
                               '<label class="relative inline-flex items-center cursor-pointer">' .
                               '<input type="checkbox" class="sr-only peer" onchange="this.form.submit()" ' . $checked . '>' .
                               '<div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[\'\'] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>' .
                               '</label></form>';
                    })
                    ->addColumn('periode_html', function ($item) {
                        if ($item->tanggal_mulai_tampil || $item->tanggal_selesai_tampil) {
                            $mulai = $item->tanggal_mulai_tampil ? $item->tanggal_mulai_tampil->format('d/m/Y') : '-';
                            $selesai = $item->tanggal_selesai_tampil ? $item->tanggal_selesai_tampil->format('d/m/Y') : '-';
                            return '<span class="text-xs text-gray-500">' . $mulai . ' &ndash; ' . $selesai . '</span>';
                        }
                        return '<span class="text-xs text-gray-500 italic">Selamanya</span>';
                    })
                    ->addColumn('aksi', function ($item) {
                        $editUrl = route('banner.edit', $item->id);
                        $destroyUrl = route('banner.destroy', $item->id);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        $namaEscaped = e(addslashes($item->judul));

                        return '<div class="inline-flex items-center gap-0.5 justify-end">' .
                               '<a href="' . $editUrl . '" title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>' .
                               '<form action="' . $destroyUrl . '" method="POST" class="inline">' . $csrf . $method .
                               '<button type="button" title="Hapus" @click="bukaModalHapus($el.closest(\'form\'), \'' . $namaEscaped . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>' .
                               '</form></div>';
                    })
                    ->rawColumns(['gambar_html', 'judul', 'urutan_html', 'status_html', 'periode_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data banner.'], 500);
            }
        }

        return view('pages.banner.index');
    }

    public function create()
    {
        try {
            return view('pages.banner.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(Banner $banner)
    {
        try {
            $banner->update(['status_aktif' => !$banner->status_aktif]);
            return redirect()->back()->with('success', 'Status banner berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'judul' => 'required|string|max:150',
                'gambar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'urutan' => 'required|integer|min:0',
                'status_aktif' => 'required|boolean',
                'tanggal_mulai_tampil' => 'nullable|date',
                'tanggal_selesai_tampil' => 'nullable|date|after_or_equal:tanggal_mulai_tampil',
            ]);

            if ($request->hasFile('gambar')) {
                $validated['gambar'] = $request->file('gambar')->store(self::GAMBAR_PATH, 'public');
            }

            $banner = Banner::create($validated);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($banner)
                ->withProperties([
                    'judul' => $validated['judul'],
                ])
                ->log('menambahkan banner');

            return redirect()
                ->route('banner.index')
                ->with('success', 'Banner berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Banner $banner)
    {
        try {
            return view('pages.banner.edit', compact('banner'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, Banner $banner)
    {
        try {
            $validated = $request->validate([
                'judul' => 'required|string|max:150',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'urutan' => 'required|integer|min:0',
                'status_aktif' => 'required|boolean',
                'tanggal_mulai_tampil' => 'nullable|date',
                'tanggal_selesai_tampil' => 'nullable|date|after_or_equal:tanggal_mulai_tampil',
            ]);

            if ($request->hasFile('gambar')) {
                if ($banner->gambar && Storage::disk('public')->exists($banner->gambar)) {
                    Storage::disk('public')->delete($banner->gambar);
                }

                $validated['gambar'] = $request->file('gambar')->store(self::GAMBAR_PATH, 'public');
            }

            $banner->update($validated);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($banner)
                ->withProperties([
                    'judul' => $validated['judul'],
                ])
                ->log('memperbarui banner');

            return redirect()
                ->route('banner.index')
                ->with('success', 'Banner berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Banner $banner)
    {
        try {
            if ($banner->gambar && Storage::disk('public')->exists($banner->gambar)) {
                Storage::disk('public')->delete($banner->gambar);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($banner)
                ->withProperties([
                    'judul' => $banner->judul,
                ])
                ->log('menghapus banner');

            $banner->delete();

            return redirect()
                ->route('banner.index')
                ->with('success', 'Banner berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}