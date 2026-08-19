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
        try {
            $search = $request->search;

            $banner = Banner::when($search, function ($query) use ($search) {
                    $query->where('judul', 'like', "%{$search}%");
                })
                ->orderBy('urutan')
                ->latest()
                ->get();

            return view('pages.banner.index', compact('banner', 'search'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
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