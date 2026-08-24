<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\RewardKatalog;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RewardVoucherController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $statusFilter = $request->status;

                $query = RewardKatalog::where('jenis', 'voucher')
                    ->when($statusFilter === 'aktif', fn ($q) => $q->where('status_aktif', true))
                    ->when($statusFilter === 'nonaktif', fn ($q) => $q->where('status_aktif', false));

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('voucher_html', fn ($item) => view('pages.reward-voucher.partials.voucher', compact('item'))->render())
                    ->editColumn('deskripsi', fn ($item) => view('pages.reward-voucher.partials.deskripsi', compact('item'))->render())
                    ->editColumn('poin_dibutuhkan', fn ($item) => number_format($item->poin_dibutuhkan, 0, ',', '.') . ' poin')
                    ->addColumn('stok_html', fn ($item) => view('pages.reward-voucher.partials.stok', compact('item'))->render())
                    ->addColumn('status_html', fn ($item) => view('pages.reward-voucher.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('pages.reward-voucher.partials.aksi', compact('item'))->render())
                    ->rawColumns(['voucher_html', 'deskripsi', 'stok_html', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data reward voucher.'], 500);
            }
        }

        return view('pages.reward-voucher.index', [
            'title' => 'Reward Voucher',
        ]);
    }

    public function create()
    {
        try {
            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();

            return view('pages.reward-voucher.create', [
                'title' => 'Tambah Reward Voucher',
                'produks' => $produks,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(RewardKatalog $rewardVoucher)
    {
        try {
            abort_unless($rewardVoucher->jenis === 'voucher', 404);

            $rewardVoucher->load('produks.kategori');

            return view('pages.reward-voucher.show', [
                'title' => 'Detail Reward Voucher',
                'voucher' => $rewardVoucher,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validasi($request);

            $pathGambar = null;
            if ($request->hasFile('gambar')) {
                $pathGambar = $request->file('gambar')->store('reward', 'public');
            }

            $voucher = RewardKatalog::create([
                'nama' => $validated['nama'],
                'jenis' => 'voucher',
                'deskripsi' => $validated['deskripsi'] ?? null,
                'gambar' => $pathGambar,
                'poin_dibutuhkan' => $validated['poin_dibutuhkan'],
                'stok' => $validated['stok'] ?? null,
                'status_aktif' => (bool) $request->boolean('status_aktif'),
                'jenis_potongan' => $validated['jenis_potongan'],
                'nominal_potongan' => $validated['jenis_potongan'] === 'nominal' ? $validated['nominal_potongan'] : 0,
                'persentase' => $validated['jenis_potongan'] === 'persentase' ? $validated['persentase'] : 0,
                'maksimal_potongan' => $validated['jenis_potongan'] === 'persentase' ? ($validated['maksimal_potongan'] ?? null) : null,
                'min_pembelian' => $validated['min_pembelian'] ?? 0,
                'masa_berlaku_hari' => $validated['masa_berlaku_hari'] ?? null,
            ]);

            $voucher->produks()->sync($validated['produk_ids']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($voucher)
                ->withProperties($voucher->toArray() + ['produk_ids' => $validated['produk_ids']])
                ->log('Menambahkan reward voucher: ' . $voucher->nama);

            return redirect()->route('reward-voucher.index')->with('success', 'Reward voucher berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(RewardKatalog $rewardVoucher)
    {
        try {
            abort_unless($rewardVoucher->jenis === 'voucher', 404);

            $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();
            $selectedProduks = $rewardVoucher->produks->pluck('id')->toArray();

            return view('pages.reward-voucher.edit', [
                'title' => 'Edit Reward Voucher',
                'voucher' => $rewardVoucher,
                'produks' => $produks,
                'selectedProduks' => $selectedProduks,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, RewardKatalog $rewardVoucher)
    {
        try {
            abort_unless($rewardVoucher->jenis === 'voucher', 404);

            $validated = $this->validasi($request);

            $dataUpdate = [
                'nama' => $validated['nama'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'poin_dibutuhkan' => $validated['poin_dibutuhkan'],
                'stok' => $validated['stok'] ?? null,
                'status_aktif' => (bool) $request->boolean('status_aktif'),
                'jenis_potongan' => $validated['jenis_potongan'],
                'nominal_potongan' => $validated['jenis_potongan'] === 'nominal' ? $validated['nominal_potongan'] : 0,
                'persentase' => $validated['jenis_potongan'] === 'persentase' ? $validated['persentase'] : 0,
                'maksimal_potongan' => $validated['jenis_potongan'] === 'persentase' ? ($validated['maksimal_potongan'] ?? null) : null,
                'min_pembelian' => $validated['min_pembelian'] ?? 0,
                'masa_berlaku_hari' => $validated['masa_berlaku_hari'] ?? null,
            ];

            if ($request->hasFile('gambar')) {
                if ($rewardVoucher->gambar && Storage::disk('public')->exists($rewardVoucher->gambar)) {
                    Storage::disk('public')->delete($rewardVoucher->gambar);
                }

                $dataUpdate['gambar'] = $request->file('gambar')->store('reward', 'public');
            }

            $rewardVoucher->update($dataUpdate);

            $rewardVoucher->produks()->sync($validated['produk_ids']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($rewardVoucher)
                ->withProperties($rewardVoucher->toArray() + ['produk_ids' => $validated['produk_ids']])
                ->log('Memperbarui reward voucher: ' . $rewardVoucher->nama);

            return redirect()->route('reward-voucher.index')->with('success', 'Reward voucher berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(RewardKatalog $rewardVoucher)
    {
        try {
            abort_unless($rewardVoucher->jenis === 'voucher', 404);

            $rewardVoucher->status_aktif = !$rewardVoucher->status_aktif;
            $rewardVoucher->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($rewardVoucher)
                ->withProperties(['status_aktif' => $rewardVoucher->status_aktif])
                ->log('Mengubah status reward voucher: ' . $rewardVoucher->nama);

            return back()->with('success', 'Status reward voucher berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(RewardKatalog $rewardVoucher)
    {
        try {
            abort_unless($rewardVoucher->jenis === 'voucher', 404);

            if ($rewardVoucher->penukaran()->exists()) {
                return back()->with('error', 'Voucher ini sudah pernah ditukar oleh member dan tidak bisa dihapus. Nonaktifkan saja agar riwayat penukaran tetap tersimpan.');
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($rewardVoucher)
                ->withProperties($rewardVoucher->toArray())
                ->log('Menghapus reward voucher: ' . $rewardVoucher->nama);

            if ($rewardVoucher->gambar && Storage::disk('public')->exists($rewardVoucher->gambar)) {
                Storage::disk('public')->delete($rewardVoucher->gambar);
            }

            $rewardVoucher->delete();

            return back()->with('success', 'Reward voucher berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'poin_dibutuhkan' => 'required|integer|min:1',
            'stok' => 'nullable|integer|min:0',
            'status_aktif' => 'nullable|boolean',
            'jenis_potongan' => 'required|in:nominal,persentase',
            'nominal_potongan' => 'required_if:jenis_potongan,nominal|nullable|numeric|min:0',
            'persentase' => 'required_if:jenis_potongan,persentase|nullable|numeric|min:0|max:100',
            'maksimal_potongan' => 'nullable|numeric|min:0',
            'min_pembelian' => 'nullable|numeric|min:0',
            'masa_berlaku_hari' => 'nullable|integer|min:1',
            'produk_ids' => 'required|array|min:1',
            'produk_ids.*' => 'exists:produks,id',
        ], [
            'nama.required' => 'Nama voucher wajib diisi.',
            'poin_dibutuhkan.required' => 'Jumlah poin yang dibutuhkan wajib diisi.',
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'gambar.max' => 'Ukuran gambar maksimal 2MB.',
            'jenis_potongan.required' => 'Jenis potongan wajib dipilih.',
            'nominal_potongan.required_if' => 'Nominal potongan wajib diisi untuk voucher jenis nominal.',
            'persentase.required_if' => 'Persentase potongan wajib diisi untuk voucher jenis persentase.',
            'produk_ids.required' => 'Pilih minimal satu produk yang mendapat reward voucher ini.',
        ]);
    }
}