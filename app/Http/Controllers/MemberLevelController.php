<?php

namespace App\Http\Controllers;

use App\Models\MemberLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MemberLevelController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->search;

            $levels = MemberLevel::when($search, function ($query) use ($search) {
                    $query->where('nama_level', 'like', "%{$search}%");
                })
                ->orderBy('minimal_poin')
                ->paginate(10);

            return view('pages.member-level.index', [
                'title' => 'Aturan Naik Level',
                'levels' => $levels,
            ]);
        } catch (\Exception $e) {
            Log::error('Error index MemberLevelController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data level membership.');
        }
    }

    public function create()
    {
        return view('pages.member-level.create', [
            'title' => 'Tambah Level Membership',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        $level = MemberLevel::create([
            'nama_level' => $validated['nama_level'],
            'minimal_poin' => $validated['minimal_poin'],
            'warna_badge' => $validated['warna_badge'] ?? 'gray',
            'status_aktif' => $request->boolean('status_aktif'),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($level)
            ->withProperties($level->toArray())
            ->log('Menambahkan level membership: ' . $level->nama_level);

        return redirect()->route('member-level.index')->with('success', 'Level membership berhasil ditambahkan.');
    }

    public function edit(MemberLevel $memberLevel)
    {
        return view('pages.member-level.edit', [
            'title' => 'Edit Level Membership',
            'level' => $memberLevel,
        ]);
    }

    public function update(Request $request, MemberLevel $memberLevel)
    {
        $validated = $this->validasi($request, $memberLevel->id);

        $memberLevel->update([
            'nama_level' => $validated['nama_level'],
            'minimal_poin' => $validated['minimal_poin'],
            'warna_badge' => $validated['warna_badge'] ?? $memberLevel->warna_badge,
            'status_aktif' => $request->boolean('status_aktif'),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($memberLevel)
            ->withProperties($memberLevel->toArray())
            ->log('Memperbarui level membership: ' . $memberLevel->nama_level);

        return redirect()->route('member-level.index')->with('success', 'Level membership berhasil diperbarui.');
    }

    public function toggleStatus(MemberLevel $memberLevel)
    {
        $akanDinonaktifkan = $memberLevel->status_aktif;

        if ($akanDinonaktifkan && MemberLevel::aktif()->count() <= 1) {
            return back()->with('error', 'Tidak bisa menonaktifkan level ini karena ini satu-satunya level aktif. Minimal harus ada 1 level aktif supaya semua member tetap punya level.');
        }

        $memberLevel->status_aktif = !$memberLevel->status_aktif;
        $memberLevel->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($memberLevel)
            ->withProperties(['status_aktif' => $memberLevel->status_aktif])
            ->log('Mengubah status level membership: ' . $memberLevel->nama_level);

        return back()->with('success', 'Status level membership berhasil diubah.');
    }

    public function destroy(MemberLevel $memberLevel)
    {
        if ($memberLevel->status_aktif && MemberLevel::aktif()->count() <= 1) {
            return back()->with('error', 'Tidak bisa menghapus level ini karena ini satu-satunya level aktif. Minimal harus ada 1 level aktif supaya semua member tetap punya level.');
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($memberLevel)
            ->withProperties($memberLevel->toArray())
            ->log('Menghapus level membership: ' . $memberLevel->nama_level);

        $memberLevel->delete();

        return back()->with('success', 'Level membership berhasil dihapus.');
    }

    private function validasi(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nama_level' => [
                'required', 'string', 'max:50',
                Rule::unique('member_levels', 'nama_level')->ignore($ignoreId),
            ],
            'minimal_poin' => [
                'required', 'integer', 'min:0',
                Rule::unique('member_levels', 'minimal_poin')->ignore($ignoreId),
            ],
            'warna_badge' => 'nullable|string|max:20',
            'status_aktif' => 'nullable|boolean',
        ], [
            'nama_level.required' => 'Nama level wajib diisi.',
            'nama_level.unique' => 'Nama level ini sudah dipakai.',
            'minimal_poin.required' => 'Minimal poin wajib diisi.',
            'minimal_poin.unique' => 'Minimal poin ini sudah dipakai level lain. Setiap level harus punya ambang batas poin yang berbeda supaya urutan tingkatannya jelas.',
        ]);
    }
}