<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanMember;
use App\Support\ActivityLogHelper;

class PengaturanMemberController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = PengaturanMember::query();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('belanja_html', fn($item) => view('pages.pengaturan-member.partials.belanja', compact('item'))->render())
                    ->addColumn('poin_html', fn($item) => view('pages.pengaturan-member.partials.poin', compact('item'))->render())
                    ->addColumn('mulai_html', fn($item) => view('pages.pengaturan-member.partials.mulai', compact('item'))->render())
                    ->addColumn('selesai_html', fn($item) => view('pages.pengaturan-member.partials.selesai', compact('item'))->render())
                    ->addColumn('status_html', fn($item) => view('pages.pengaturan-member.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.pengaturan-member.partials.aksi', compact('item'))->render())
                    ->rawColumns(['belanja_html', 'poin_html', 'mulai_html', 'selesai_html', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data pengaturan member.'], 500);
            }
        }

        return view('pages.pengaturan-member.index');
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
