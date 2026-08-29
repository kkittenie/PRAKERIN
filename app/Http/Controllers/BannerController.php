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
                // Query dibiarkan tanpa orderBy manual di sini. Kalau tetap
                // pakai ->orderBy('urutan')->latest(), ORDER BY itu nempel
                // duluan di query dan urutan dari klik sort header DataTables
                // cuma ditambahin di belakangnya — jadi kealah dan keliatan
                // kayak sort-nya gak ngefek. Urutan default awal sekarang
                // diatur dari sisi JS (lihat opsi `order` di index.blade.php).
                $query = Banner::query();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('kode_html', fn($item) => view('pages.banner.partials.kode', compact('item'))->render())
                    ->addColumn('gambar_html', fn($item) => view('pages.banner.partials.gambar', compact('item'))->render())
                    ->editColumn('judul', fn($item) => view('pages.banner.partials.judul', compact('item'))->render())
                    ->addColumn('urutan_html', fn($item) => view('pages.banner.partials.urutan', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.banner.partials.status', compact('item'))->render())
                    ->addColumn('periode_html', fn($item) => view('pages.banner.partials.periode', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.banner.partials.aksi', compact('item'))->render())
                    ->rawColumns(['kode_html', 'gambar_html', 'judul', 'urutan_html', 'status_html', 'periode_html', 'aksi'])
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

            $dataInsert = $validated;
            unset($dataInsert['gambar']);
            $dataInsert['gambar'] = '';

            $banner = Banner::create($dataInsert);

            $kodeBanner = 'BNR' . str_pad((string) $banner->id, 3, '0', STR_PAD_LEFT);
            $pathGambar = null;
            if ($request->hasFile('gambar')) {
                $pathGambar = $request->file('gambar')->store('banner/' . $kodeBanner, 'public');
            }

            $banner->update([
                'kode_banner' => $kodeBanner,
                'gambar' => $pathGambar ?: '',
            ]);

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
                $kodeBanner = $banner->kode_banner;
                if ($kodeBanner) {
                    Storage::disk('public')->deleteDirectory('banner/' . $kodeBanner);
                }

                $validated['gambar'] = $request->file('gambar')->store('banner/' . $kodeBanner, 'public');
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
            $kodeBanner = $banner->kode_banner;
            if ($kodeBanner) {
                Storage::disk('public')->deleteDirectory('banner/' . $kodeBanner);
            } elseif ($banner->gambar && Storage::disk('public')->exists($banner->gambar)) {
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