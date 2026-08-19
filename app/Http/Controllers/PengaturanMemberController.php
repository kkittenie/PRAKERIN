<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanMember;
use App\Support\ActivityLogHelper;

class PengaturanMemberController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = PengaturanMember::query();

            if ($request->filled('search')) {
                $query->where('nama_aturan', 'like', '%' . $request->search . '%');
            }

            $pengaturanMember = $query
                ->latest()
                ->get();
            
            return view('pages.pengaturan-member.index', compact('pengaturanMember'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            return view('pages.pengaturan-member.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_aturan' => 'required|string|max:255',
                'minimal_belanja' => 'required|numeric|min:1',
                'poin' => 'required|integer|min:1',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'nullable|date|after_or_equal:mulai_berlaku',
            ]);

            $pengaturanMember = PengaturanMember::create([
                'nama_aturan' => $request->nama_aturan,
                'minimal_belanja' => $request->minimal_belanja,
                'poin' => $request->poin,
                'mulai_berlaku' => $request->mulai_berlaku,
                'selesai_berlaku' => $request->selesai_berlaku,
                'status_aktif' => true,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pengaturanMember)
                ->withProperties($pengaturanMember->toArray())
                ->log('Menambahkan pengaturan member baru: ' . $pengaturanMember->nama_aturan);

            return redirect()
                ->route('pengaturan-member.index')
                ->with('success', 'Pengaturan member berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(PengaturanMember $pengaturanMember, $id)
    {
        try {
            $pengaturanMember = PengaturanMember::findOrFail($id);
            return view('pages.pengaturan-member.edit', compact('pengaturanMember'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nama_aturan' => 'required|string|max:255',
                'minimal_belanja' => 'required|numeric|min:1',
                'poin' => 'required|integer|min:1',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'nullable|date|after_or_equal:mulai_berlaku',
            ]);

            $pengaturanMember = PengaturanMember::findOrFail($id);

            $pengaturanMember->update([
                'nama_aturan' => $request->nama_aturan,
                'minimal_belanja' => $request->minimal_belanja,
                'poin' => $request->poin,
                'mulai_berlaku' => $request->mulai_berlaku,
                'selesai_berlaku' => $request->selesai_berlaku,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pengaturanMember)
                ->withProperties($pengaturanMember->toArray())
                ->log('Memperbarui pengaturan member: ' . $pengaturanMember->nama_aturan);

            return redirect()
                ->route('pengaturan-member.index')
                ->with('success', 'Pengaturan member berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(PengaturanMember $pengaturanMember, $id)
    {
        try {
            $pengaturanMember = PengaturanMember::findOrFail($id);

            $namaAturan = $pengaturanMember->nama_aturan;
            $pengaturanMember->delete();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pengaturanMember)
                ->withProperties($pengaturanMember->toArray())
                ->log('Menghapus pengaturan member: ' . $pengaturanMember->nama_aturan);

            return back()->with('success', 'Pengaturan member berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(PengaturanMember $pengaturanMember, $id)
    {
        try {
            $pengaturanMember = PengaturanMember::findOrFail($id);

            $pengaturanMember->status_aktif = ! $pengaturanMember->status_aktif;
            $pengaturanMember->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pengaturanMember)
                ->withProperties([
                    'status_aktif' => $pengaturanMember->status_aktif,
                ])
                ->log(
                    'Mengubah status pengaturan member: ' .
                    $pengaturanMember->nama_aturan .
                    ' menjadi ' .
                    ($pengaturanMember->status_aktif ? 'Aktif' : 'Nonaktif')
                );

            return back()->with('success', 'Status pengaturan member berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
