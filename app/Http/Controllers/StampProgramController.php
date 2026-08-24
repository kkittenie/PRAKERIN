<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\StampProgram;
use App\Models\StampProgramHadiah;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StampProgramController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $statusFilter = $request->status;

                $query = StampProgram::withCount('hadiah')
                    ->when($statusFilter === 'aktif', fn ($q) => $q->where('status_aktif', true))
                    ->when($statusFilter === 'nonaktif', fn ($q) => $q->where('status_aktif', false));

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('minimal_pembelian', fn ($item) => 'Rp ' . number_format($item->minimal_pembelian, 0, ',', '.'))
                    ->editColumn('jumlah_stamp_dibutuhkan', fn ($item) => $item->jumlah_stamp_dibutuhkan . ' stamp')
                    ->addColumn('hadiah_html', fn ($item) => $item->hadiah_count . ' produk')
                    ->addColumn('redeem_html', fn ($item) => view('pages.stamp-program.partials.redeem', compact('item'))->render())
                    ->addColumn('status_html', fn ($item) => view('pages.stamp-program.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('pages.stamp-program.partials.aksi', compact('item'))->render())
                    ->orderColumn('redeem_html', function ($query, $order) {
                        $query->orderBy('maksimal_redeem', $order)->orderBy('jumlah_redeem', $order);
                    })
                    ->rawColumns(['hadiah_html', 'redeem_html', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data program stamp.'], 500);
            }
        }

        return view('pages.stamp-program.index', [
            'title' => 'Reward Stamp',
        ]);
    }

    public function create()
    {
        try {
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk')->get();

            return view('pages.stamp-program.create', [
                'title' => 'Tambah Reward Stamp',
                'produks' => $produks,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(StampProgram $stampProgram)
    {
        try {
            $stampProgram->load(['hadiah.produk', 'redemptions']);

            return view('pages.stamp-program.show', [
                'title' => 'Detail Reward Stamp',
                'program' => $stampProgram,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(StampProgram $stampProgram)
    {
        try {
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk')->get();
            $stampProgram->load('hadiah.produk');

            return view('pages.stamp-program.edit', [
                'title' => 'Edit Reward Stamp',
                'program' => $stampProgram,
                'produks' => $produks,
                'hadiahTerpilih' => $stampProgram->hadiah,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, StampProgram $stampProgram)
    {
        try {
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(StampProgram $stampProgram)
    {
        try {
            $akanAktif = !$stampProgram->status_aktif;

            if ($akanAktif && $stampProgram->tanggal_berakhir && now()->startOfDay()->gt($stampProgram->tanggal_berakhir)) {
                return back()->with('error', 'Program "' . $stampProgram->nama_program . '" tidak bisa diaktifkan karena tanggal berakhirnya (' . $stampProgram->tanggal_berakhir->format('d-m-Y') . ') sudah lewat. Ubah dulu tanggal berakhirnya lewat menu Edit.');
            }

            DB::transaction(function () use ($stampProgram, $akanAktif) {
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

            $pesan = $akanAktif
                ? 'Program stamp diaktifkan. Program aktif lainnya (jika ada) otomatis dinonaktifkan karena cuma 1 yang boleh aktif.'
                : 'Program stamp dinonaktifkan.';

            return back()->with('success', $pesan);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(StampProgram $stampProgram)
    {
        try {
            if ($stampProgram->redemptions()->exists()) {
                return back()->with('error', 'Program stamp ini sudah pernah ditukar member dan tidak bisa dihapus. Nonaktifkan saja agar riwayat penukaran tetap tersimpan.');
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($stampProgram)
                ->withProperties($stampProgram->toArray())
                ->log('Menghapus program stamp: ' . $stampProgram->nama_program);

            $stampProgram->delete();

            return back()->with('success', 'Program stamp berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
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
            'status_aktif' => [
                'nullable',
                'boolean',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$value) {
                        return;
                    }

                    $tanggalBerakhir = $request->input('tanggal_berakhir');

                    if ($tanggalBerakhir && now()->startOfDay()->gt(\Carbon\Carbon::parse($tanggalBerakhir))) {
                        $fail('Program tidak bisa diaktifkan karena tanggal berakhir yang diisi sudah lewat. Ubah tanggal berakhir ke tanggal yang belum lewat, atau jangan aktifkan sekarang.');
                    }
                },
            ],
            'produk_hadiah' => 'required|array|min:1',
            'produk_hadiah.*' => 'required|distinct|exists:produks,id',
            'jumlah_hadiah' => 'required|array|min:1',
            'jumlah_hadiah.*' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/\d+/', $attribute, $match);
                    $index = $match[0] ?? null;
                    $produkId = $request->produk_hadiah[$index] ?? null;

                    if (!$produkId) {
                        return;
                    }

                    $produk = Produk::find($produkId);

                    if ($produk && $value > $produk->stok) {
                        $fail("Jumlah hadiah untuk \"{$produk->nama_produk}\" melebihi stok yang tersedia (stok saat ini: {$produk->stok}).");
                    }
                },
            ],
        ], [
            'nama_program.required' => 'Nama program wajib diisi.',
            'minimal_pembelian.required' => 'Minimal pembelian per stamp wajib diisi.',
            'minimal_pembelian.min' => 'Minimal pembelian minimal Rp 1.000.',
            'jumlah_stamp_dibutuhkan.required' => 'Jumlah stamp yang dibutuhkan wajib diisi.',
            'produk_hadiah.required' => 'Pilih minimal 1 produk sebagai pilihan hadiah.',
            'produk_hadiah.*.distinct' => 'Produk hadiah tidak boleh dipilih lebih dari sekali.',
            'jumlah_hadiah.*.required' => 'Isi jumlah untuk setiap hadiah yang dipilih.',
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ]);
    }
}