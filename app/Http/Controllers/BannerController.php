<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    private const GAMBAR_PATH = 'banner/images';

    public function index(Request $request)
    {
        $search = $request->search;

        $banner = Banner::when($search, function ($query) use ($search) {
                $query->where('judul', 'like', "%{$search}%");
            })
            ->orderBy('urutan')
            ->latest()
            ->paginate(10);

        return view('pages.banner.index', compact('banner', 'search'));
    }

    public function create()
    {
        return view('pages.banner.create');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['status_aktif' => !$banner->status_aktif]);
        return redirect()->back()->with('success', 'Status banner berhasil diubah.');
    }

    public function store(Request $request)
    {
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
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Banner $banner)
    {
        return view('pages.banner.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
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
    }

    public function destroy(Banner $banner)
    {
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
    }
}