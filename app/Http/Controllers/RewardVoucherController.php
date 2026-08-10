<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\RewardKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RewardVoucherController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->search;
            $statusFilter = $request->status;

            $vouchers = RewardKatalog::where('jenis', 'voucher')
                ->when($search, function ($query) use ($search) {
                    $query->where('nama', 'like', "%{$search}%");
                })
                ->when($statusFilter === 'aktif', fn ($q) => $q->where('status_aktif', true))
                ->when($statusFilter === 'nonaktif', fn ($q) => $q->where('status_aktif', false))
                ->latest()
                ->paginate(10);

            return view('pages.reward-voucher.index', [
                'title' => 'Reward Voucher',
                'vouchers' => $vouchers,
            ]);
        } catch (\Exception $e) {
            Log::error('Error index RewardVoucherController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data reward voucher.');
        }
    }

    public function create()
    {
        $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();

        return view('pages.reward-voucher.create', [
            'title' => 'Tambah Reward Voucher',
            'produks' => $produks,
        ]);
    }

    public function show(RewardKatalog $rewardVoucher)
    {
        abort_unless($rewardVoucher->jenis === 'voucher', 404);

        $rewardVoucher->load('produks.kategori');

        return view('pages.reward-voucher.show', [
            'title' => 'Detail Reward Voucher',
            'voucher' => $rewardVoucher,
        ]);
    }

    public function store(Request $request)
    {
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
    }

    public function edit(RewardKatalog $rewardVoucher)
    {
        abort_unless($rewardVoucher->jenis === 'voucher', 404);

        $produks = Produk::where('status_aktif', true)->orderBy('nama_produk', 'asc')->get();
        $selectedProduks = $rewardVoucher->produks->pluck('id')->toArray();

        return view('pages.reward-voucher.edit', [
            'title' => 'Edit Reward Voucher',
            'voucher' => $rewardVoucher,
            'produks' => $produks,
            'selectedProduks' => $selectedProduks,
        ]);
    }

    public function update(Request $request, RewardKatalog $rewardVoucher)
    {
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
            // Hapus gambar lama dulu supaya file lama tidak menumpuk di server
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
    }

    public function toggleStatus(RewardKatalog $rewardVoucher)
    {
        abort_unless($rewardVoucher->jenis === 'voucher', 404);

        $rewardVoucher->status_aktif = !$rewardVoucher->status_aktif;
        $rewardVoucher->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($rewardVoucher)
            ->withProperties(['status_aktif' => $rewardVoucher->status_aktif])
            ->log('Mengubah status reward voucher: ' . $rewardVoucher->nama);

        return back()->with('success', 'Status reward voucher berhasil diubah.');
    }

    public function destroy(RewardKatalog $rewardVoucher)
    {
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