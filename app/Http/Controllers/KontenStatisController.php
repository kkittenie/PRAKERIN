<?php

namespace App\Http\Controllers;

use App\Models\KontenStatis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KontenStatisController extends Controller
{
    protected array $slugDiizinkan = [
        'help-center'       => 'Help Center',
        'terms-conditions'  => 'Terms and Conditions',
        'privacy-policy'    => 'Privacy Policy',
    ];

    public function index()
    {
        $kontenList = KontenStatis::whereIn('slug', array_keys($this->slugDiizinkan))->get()->keyBy('slug');
        return view('pages.konten-statis.index', compact('kontenList'));
    }

    public function edit($slug)
    {
        if (!array_key_exists($slug, $this->slugDiizinkan)) {
            abort(404);
        }

        $konten = KontenStatis::firstOrNew(
            ['slug' => $slug],
            ['judul' => $this->slugDiizinkan[$slug], 'konten' => '']
        );

        $judul = $this->slugDiizinkan[$slug];

        return view('pages.konten-statis.edit', compact('konten', 'judul', 'slug'));
    }

    public function update(Request $request, $slug)
    {
        if (!array_key_exists($slug, $this->slugDiizinkan)) {
            abort(404);
        }

        try {
            $validated = $request->validate([
                'judul'  => 'required|string|max:255',
                'konten' => 'nullable|string',
            ]);

            KontenStatis::updateOrCreate(
                ['slug' => $slug],
                [
                    'judul'  => $validated['judul'],
                    'konten' => $validated['konten'] ?? '',
                ]
            );

            activity()
                ->causedBy(auth()->user())
                ->log('memperbarui konten statis: ' . $slug);

            return redirect()
                ->route('konten-statis.index')
                ->with('success', 'Konten "' . $this->slugDiizinkan[$slug] . '" berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('Error update KontenStatisController: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan konten.');
        }
    }
}
