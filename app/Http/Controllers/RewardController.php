<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->search;
            $statusFilter = $request->status; // 'aktif' | 'nonaktif'

            $rewards = Reward::with('produk')
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nama_reward', 'like', "%{$search}%")
                            ->orWhereHas('produk', function ($qp) use ($search) {
                                $qp->where('nama_produk', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($statusFilter === 'aktif', fn($q) => $q->where('status_aktif', true))
                ->when($statusFilter === 'nonaktif', fn($q) => $q->where('status_aktif', false))
                ->latest()
                ->paginate(10);

            return view('pages.reward.index', [
                'title' => 'Reward Produk',
                'rewards' => $rewards,
            ]);
        } catch (\Exception $e) {
            Log::error('Error index RewardController: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data reward.');
        }
    }

    public function create()
    {
        $produks = Produk::where('status_aktif', true)->orderBy('nama_produk')->get();
        return view('pages.reward.create', ['title' => 'Tambah Reward Produk', 'produks' => $produks]);
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        $reward = Reward::create([
            'nama_reward' => $validated['nama_reward'],
            'tipe_reward' => $validated['tipe_reward'] ?? 'produk_gratis',
            'produk_id' => $validated['produk_id'],
            'poin_dibutuhkan' => $validated['poin_dibutuhkan'],
            'stok_reward' => $validated['stok_reward'],
            'maksimal_redeem' => $validated['maksimal_redeem'] ?? null,
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'tanggal_berakhir' => $validated['tanggal_berakhir'] ?? null,
            'status_aktif' => $request->has('status_aktif') ? (bool) $request->status_aktif : true,
        ]);

        activity()->causedBy(auth()->user())->performedOn($reward)
            ->withProperties($reward->toArray())
            ->log('Menambahkan reward produk: ' . $reward->nama_reward);

        return redirect()->route('reward.index')->with('success', 'Reward produk berhasil ditambahkan.');
    }

    public function edit(Reward $reward)
    {
        $produks = Produk::where('status_aktif', true)->orderBy('nama_produk')->get();
        return view('pages.reward.edit', ['title' => 'Edit Reward Produk', 'reward' => $reward, 'produks' => $produks]);
    }

    public function update(Request $request, Reward $reward)
    {
        $validated = $this->validasi($request);

        $reward->update([
            'nama_reward' => $validated['nama_reward'],
            'tipe_reward' => $validated['tipe_reward'] ?? $reward->tipe_reward,
            'produk_id' => $validated['produk_id'],
            'poin_dibutuhkan' => $validated['poin_dibutuhkan'],
            'stok_reward' => $validated['stok_reward'],
            'maksimal_redeem' => $validated['maksimal_redeem'] ?? null,
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'tanggal_berakhir' => $validated['tanggal_berakhir'] ?? null,
            'status_aktif' => $request->has('status_aktif') ? (bool) $request->status_aktif : false,
        ]);

        activity()->causedBy(auth()->user())->performedOn($reward)
            ->withProperties($reward->toArray())
            ->log('Memperbarui reward produk: ' . $reward->nama_reward);

        return redirect()->route('reward.index')->with('success', 'Reward produk berhasil diperbarui.');
    }

    public function toggleStatus(Reward $reward)
    {
        $reward->status_aktif = !$reward->status_aktif;
        $reward->save();

        activity()->causedBy(auth()->user())->performedOn($reward)
            ->withProperties(['status_aktif' => $reward->status_aktif])
            ->log('Mengubah status reward: ' . $reward->nama_reward);

        return back()->with('success', 'Status reward berhasil diubah.');
    }

    public function destroy(Reward $reward)
    {
        activity()->causedBy(auth()->user())->performedOn($reward)
            ->withProperties($reward->toArray())
            ->log('Menghapus reward produk: ' . $reward->nama_reward);

        $reward->delete();

        return back()->with('success', 'Reward produk berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama_reward' => 'required|string|max:255',
            'tipe_reward' => 'nullable|in:produk_gratis,produk_diskon',
            'produk_id' => 'required|exists:produks,id',
            'poin_dibutuhkan' => 'required|integer|min:1',
            'stok_reward' => 'required|integer|min:0',
            'maksimal_redeem' => 'nullable|integer|min:1',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_aktif' => 'nullable|boolean',
        ]);
    }
}