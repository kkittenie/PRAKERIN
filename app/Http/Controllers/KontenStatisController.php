<?php

namespace App\Http\Controllers;

use App\Models\KontenStatis;
use App\Support\ActivityLogHelper;
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
        try {
            $kontenList = KontenStatis::whereIn('slug', array_keys($this->slugDiizinkan))->get()->keyBy('slug');
            return view('pages.konten-statis.index', compact('kontenList'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit($slug)
    {
        try {
            if (!array_key_exists($slug, $this->slugDiizinkan)) {
                abort(404);
            }

            $konten = KontenStatis::firstOrNew(
                ['slug' => $slug],
                ['judul' => $this->slugDiizinkan[$slug], 'konten' => '']
            );

            $judul = $this->slugDiizinkan[$slug];

            return view('pages.konten-statis.edit', compact('konten', 'judul', 'slug'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, $slug)
    {
        try {
            if (!array_key_exists($slug, $this->slugDiizinkan)) {
                abort(404);
            }

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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
