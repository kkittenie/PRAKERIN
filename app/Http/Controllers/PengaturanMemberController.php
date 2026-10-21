<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanMember;
use App\Support\ActivityLogHelper;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PengaturanMemberController extends Controller
{
    private function cariBentrokAktif($mulai, $selesai, ?int $kecualiId = null): ?PengaturanMember
    {
        $mulaiBaru = Carbon::parse($mulai);
        $selesaiBaru = $selesai ? Carbon::parse($selesai) : null;
        $takTerhingga = Carbon::create(9999, 12, 31, 23, 59, 59);

        $query = PengaturanMember::where('status_aktif', true);
        if ($kecualiId) {
            $query->where('id', '!=', $kecualiId);
        }

        foreach ($query->get() as $aturan) {
            $mulaiLain = Carbon::parse($aturan->mulai_berlaku);
            $selesaiLain = $aturan->selesai_berlaku ? Carbon::parse($aturan->selesai_berlaku) : null;

            $bentrok = $mulaiBaru->lte($selesaiLain ?? $takTerhingga)
                && $mulaiLain->lte($selesaiBaru ?? $takTerhingga);

            if ($bentrok) {
                return $aturan;
            }
        }

        return null;
    }

    private function labelRentang(PengaturanMember $aturan): string
    {
        $mulai = Carbon::parse($aturan->mulai_berlaku)->format('d M Y H:i');
        $selesai = $aturan->selesai_berlaku
            ? Carbon::parse($aturan->selesai_berlaku)->format('d M Y H:i')
            : 'selamanya';

        return "{$mulai} - {$selesai}";
    }
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

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data pengaturan poin.'], 500);
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
            $validated = $request->validate([
                'nama_aturan' => 'required|string|max:255',
                'minimal_belanja' => 'required|numeric|min:1',
                'poin' => 'required|integer|min:1',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'nullable|date|after_or_equal:mulai_berlaku',
                'status_aktif' => 'nullable|boolean',
            ]);

            $statusAktif = (bool) ($validated['status_aktif'] ?? false);

            if ($statusAktif) {
                $bentrok = $this->cariBentrokAktif($validated['mulai_berlaku'], $validated['selesai_berlaku'] ?? null);
                if ($bentrok) {
                    throw ValidationException::withMessages([
                        'status_aktif' => "Tidak bisa diaktifkan, rentang tanggalnya bentrok dengan aturan aktif \"{$bentrok->nama_aturan}\" ({$this->labelRentang($bentrok)}).",
                    ]);
                }
            }

            $pengaturanMember = PengaturanMember::create([
                'nama_aturan' => $validated['nama_aturan'],
                'minimal_belanja' => $validated['minimal_belanja'],
                'poin' => $validated['poin'],
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'] ?? null,
                'status_aktif' => $statusAktif,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pengaturanMember)
                ->withProperties($pengaturanMember->toArray())
                ->log('Menambahkan pengaturan poin baru: ' . $pengaturanMember->nama_aturan);

            return redirect()
                ->route('pengaturan-member.index')
                ->with('success', 'Pengaturan poin berhasil ditambahkan.');
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
            $validated = $request->validate([
                'nama_aturan' => 'required|string|max:255',
                'minimal_belanja' => 'required|numeric|min:1',
                'poin' => 'required|integer|min:1',
                'mulai_berlaku' => 'required|date',
                'selesai_berlaku' => 'nullable|date|after_or_equal:mulai_berlaku',
                'status_aktif' => 'nullable|boolean',
            ]);

            $pengaturanMember = PengaturanMember::findOrFail($id);
            $statusAktif = (bool) ($validated['status_aktif'] ?? false);

            if ($statusAktif) {
                $bentrok = $this->cariBentrokAktif(
                    $validated['mulai_berlaku'],
                    $validated['selesai_berlaku'] ?? null,
                    $pengaturanMember->id
                );
                if ($bentrok) {
                    throw ValidationException::withMessages([
                        'status_aktif' => "Tidak bisa diaktifkan, rentang tanggalnya bentrok dengan aturan aktif \"{$bentrok->nama_aturan}\" ({$this->labelRentang($bentrok)}).",
                    ]);
                }
            }

            $pengaturanMember->update([
                'nama_aturan' => $validated['nama_aturan'],
                'minimal_belanja' => $validated['minimal_belanja'],
                'poin' => $validated['poin'],
                'mulai_berlaku' => $validated['mulai_berlaku'],
                'selesai_berlaku' => $validated['selesai_berlaku'] ?? null,
                'status_aktif' => $statusAktif,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pengaturanMember)
                ->withProperties($pengaturanMember->toArray())
                ->log('Memperbarui pengaturan poin: ' . $pengaturanMember->nama_aturan);

            return redirect()
                ->route('pengaturan-member.index')
                ->with('success', 'Pengaturan poin berhasil diperbarui.');
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
                ->log('Menghapus pengaturan poin: ' . $pengaturanMember->nama_aturan);

            return back()->with('success', 'Pengaturan poin berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(PengaturanMember $pengaturanMember, $id)
    {
        try {
            $pengaturanMember = PengaturanMember::findOrFail($id);
            $akanAktif = ! $pengaturanMember->status_aktif;

            if ($akanAktif) {
                $bentrok = $this->cariBentrokAktif(
                    $pengaturanMember->mulai_berlaku,
                    $pengaturanMember->selesai_berlaku,
                    $pengaturanMember->id
                );
                if ($bentrok) {
                    return back()->with(
                        'error',
                        "\"{$pengaturanMember->nama_aturan}\" tidak bisa diaktifkan, rentang tanggalnya bentrok dengan aturan aktif \"{$bentrok->nama_aturan}\" ({$this->labelRentang($bentrok)})."
                    );
                }
            }

            $pengaturanMember->status_aktif = $akanAktif;
            $pengaturanMember->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pengaturanMember)
                ->withProperties([
                    'status_aktif' => $pengaturanMember->status_aktif,
                ])
                ->log(
                    'Mengubah status pengaturan poin: ' .
                    $pengaturanMember->nama_aturan .
                    ' menjadi ' .
                    ($pengaturanMember->status_aktif ? 'Aktif' : 'Nonaktif')
                );

            return back()->with('success', 'Status pengaturan poin berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}