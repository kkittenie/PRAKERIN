<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\StampProgram;
use App\Models\StampProgramHadiah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StampProgramController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->search;
            $statusFilter = $request->status;

            $programs = StampProgram::withCount('hadiah')
                ->when($search, function ($query) use ($search) {
                    $query->where('nama_program', 'like', "%{$search}%");
                })
                ->when($statusFilter === 'aktif', fn ($q) => $q->where('status_aktif', true))
                ->when($statusFilter === 'nonaktif', fn ($q) => $q->where('status_aktif', false))
                ->latest()
                ->paginate(10);

            return view('pages.stamp-program.index', [
                'title' => 'Reward Stamp',
                'programs' => $programs,
            ]);
        } catch (\Exception $e) {
            Log::error('Error index StampProgramController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data program stamp.');
        }
    }

    public function create()
    {
        $produks = Produk::where('status_aktif', true)->orderBy('nama_produk')->get();

        return view('pages.stamp-program.create', [
            'title' => 'Tambah Reward Stamp',
            'produks' => $produks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        DB::transaction(function () use ($validated, $request) {
            if (!empty($validated['status_aktif'])) {
                StampProgram::where('status_aktif', true)->update(['status_aktif' => false]);
            }

            $program = StampProgram::create([
                'nama_program' => $validated['nama_program'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'minimal_pembelian' => $validated['minimal_pembelian'],
                'jumlah_stamp_dibutuhkan' => $validated['jumlah_stamp_dibutuhkan'],
                'maksimal_redeem' => $validated['maksimal_redeem'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                'tanggal_berakhir' => $validated['tanggal_berakhir'] ?? null,
                'status_aktif' => (bool) ($validated['status_aktif'] ?? false),
            ]);

            $produkIds = $request->input('produk_hadiah', []);
            $jumlahHadiah = $request->input('jumlah_hadiah', []);

            foreach ($produkIds as $index => $produkId) {
                StampProgramHadiah::create([
                    'stamp_program_id' => $program->id,
                    'produk_id' => $produkId,
                    'jumlah' => $jumlahHadiah[$index] ?? 1,
                ]);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($program)
                ->withProperties($program->toArray())
                ->log('Menambahkan program stamp: ' . $program->nama_program);
        });

        return redirect()->route('stamp-program.index')->with('success', 'Program stamp berhasil ditambahkan.');
    }

    public function edit(StampProgram $stampProgram)
    {
        $produks = Produk::where('status_aktif', true)->orderBy('nama_produk')->get();
        $stampProgram->load('hadiah.produk');

        return view('pages.stamp-program.edit', [
            'title' => 'Edit Reward Stamp',
            'program' => $stampProgram,
            'produks' => $produks,
            'hadiahTerpilih' => $stampProgram->hadiah,
        ]);
    }

    public function update(Request $request, StampProgram $stampProgram)
    {
        $validated = $this->validasi($request);

        DB::transaction(function () use ($validated, $request, $stampProgram) {
            if (!empty($validated['status_aktif'])) {
                StampProgram::where('id', '!=', $stampProgram->id)
                    ->where('status_aktif', true)
                    ->update(['status_aktif' => false]);
            }

            $stampProgram->update([
                'nama_program' => $validated['nama_program'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'minimal_pembelian' => $validated['minimal_pembelian'],
                'jumlah_stamp_dibutuhkan' => $validated['jumlah_stamp_dibutuhkan'],
                'maksimal_redeem' => $validated['maksimal_redeem'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                'tanggal_berakhir' => $validated['tanggal_berakhir'] ?? null,
                'status_aktif' => (bool) ($validated['status_aktif'] ?? false),
            ]);

            StampProgramHadiah::where('stamp_program_id', $stampProgram->id)->delete();

            $produkIds = $request->input('produk_hadiah', []);
            $jumlahHadiah = $request->input('jumlah_hadiah', []);

            foreach ($produkIds as $index => $produkId) {
                StampProgramHadiah::create([
                    'stamp_program_id' => $stampProgram->id,
                    'produk_id' => $produkId,
                    'jumlah' => $jumlahHadiah[$index] ?? 1,
                ]);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($stampProgram)
                ->withProperties($stampProgram->toArray())
                ->log('Memperbarui program stamp: ' . $stampProgram->nama_program);
        });

        return redirect()->route('stamp-program.index')->with('success', 'Program stamp berhasil diperbarui.');
    }

    public function toggleStatus(StampProgram $stampProgram)
    {
        DB::transaction(function () use ($stampProgram) {
            $akanAktif = !$stampProgram->status_aktif;

            if ($akanAktif) {
                StampProgram::where('id', '!=', $stampProgram->id)
                    ->where('status_aktif', true)
                    ->update(['status_aktif' => false]);
            }

            $stampProgram->status_aktif = $akanAktif;
            $stampProgram->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($stampProgram)
                ->withProperties(['status_aktif' => $stampProgram->status_aktif])
                ->log('Mengubah status program stamp: ' . $stampProgram->nama_program);
        });

        return back()->with('success', 'Status program stamp berhasil diubah. Ingat, cuma 1 program yang boleh aktif dalam satu waktu.');
    }

    public function destroy(StampProgram $stampProgram)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($stampProgram)
            ->withProperties($stampProgram->toArray())
            ->log('Menghapus program stamp: ' . $stampProgram->nama_program);

        $stampProgram->delete();

        return back()->with('success', 'Program stamp berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama_program' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'minimal_pembelian' => 'required|integer|min:1000',
            'jumlah_stamp_dibutuhkan' => 'required|integer|min:1',
            'maksimal_redeem' => 'nullable|integer|min:1',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_aktif' => 'nullable|boolean',
            'produk_hadiah' => 'required|array|min:1',
            'produk_hadiah.*' => 'exists:produks,id',
            'jumlah_hadiah' => 'required|array|min:1',
            'jumlah_hadiah.*' => 'required|integer|min:1',
        ], [
            'nama_program.required' => 'Nama program wajib diisi.',
            'minimal_pembelian.required' => 'Minimal pembelian per stamp wajib diisi.',
            'minimal_pembelian.min' => 'Minimal pembelian minimal Rp 1.000.',
            'jumlah_stamp_dibutuhkan.required' => 'Jumlah stamp yang dibutuhkan wajib diisi.',
            'produk_hadiah.required' => 'Pilih minimal 1 produk sebagai pilihan hadiah.',
            'jumlah_hadiah.*.required' => 'Isi jumlah untuk setiap hadiah yang dipilih.',
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ]);
    }
}