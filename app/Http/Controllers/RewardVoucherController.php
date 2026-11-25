<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Produk;
use App\Models\RewardKatalog;
use App\Models\RewardKatalogProduk;
use App\Models\VoucherUsage;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
                    ->addColumn('kode_html', fn ($item) => view('admin.reward-voucher.partials.kode', compact('item'))->render())
                    ->addColumn('voucher_html', fn ($item) => view('admin.reward-voucher.partials.voucher', compact('item'))->render())
                    ->editColumn('deskripsi', fn ($item) => view('admin.reward-voucher.partials.deskripsi', compact('item'))->render())
                    ->addColumn('target_html', fn ($item) => view('admin.reward-voucher.partials.target', compact('item'))->render())
                    ->addColumn('status_html', fn ($item) => view('admin.reward-voucher.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('admin.reward-voucher.partials.aksi', compact('item'))->render())
                    ->rawColumns(['kode_html', 'voucher_html', 'deskripsi', 'target_html', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data reward voucher.'], 500);
            }
        }

        return view('admin.reward-voucher.index', [
            'title' => 'Reward Voucher',
        ]);
    }

    public function create()
    {
        try {
            $produks = Produk::with(['detailProduks', 'varianProduks'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();
            $members = Member::orderBy('nama', 'asc')->get();

            return view('admin.reward-voucher.create', [
                'title' => 'Tambah Reward Voucher',
                'produks' => $this->dataProdukUntukForm($produks),
                'members' => $members,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(RewardKatalog $rewardVoucher)
    {
        try {
            abort_unless($rewardVoucher->jenis === 'voucher', 404);

            $rewardVoucher->load(['produks.kategori', 'rewardProduks.detailProduk', 'rewardProduks.varianProduk', 'members', 'usages.member', 'usages.transaksi']);

            return view('admin.reward-voucher.show', [
                'title' => 'Detail Reward Voucher',
                'voucher' => $rewardVoucher,
                'allMembers' => Member::orderBy('nama', 'asc')->get(),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validasi($request);

            $voucher = RewardKatalog::create([
                'nama' => $validated['nama'],
                'jenis' => 'voucher',
                'deskripsi' => $validated['deskripsi'] ?? null,
                'gambar' => null,
                'poin_dibutuhkan' => 0,
                'stok' => null,
                'status_aktif' => (bool) $request->boolean('status_aktif'),
                'target_member' => $validated['target_member'],
                'batas_penggunaan_per_member' => $validated['batas_penggunaan_per_member'] ?? 1,
                'jenis_potongan' => $validated['jenis_potongan'],
                'nominal_potongan' => $validated['jenis_potongan'] === 'nominal' ? $validated['nominal_potongan'] : 0,
                'persentase' => $validated['jenis_potongan'] === 'persentase' ? $validated['persentase'] : 0,
                'maksimal_potongan' => $validated['jenis_potongan'] === 'persentase' ? ($validated['maksimal_potongan'] ?? null) : null,
                'min_pembelian' => $validated['min_pembelian'] ?? 0,
                'masa_berlaku_hari' => null,
            ]);

            $kodeReward = 'RWD' . str_pad((string) $voucher->id, 3, '0', STR_PAD_LEFT);
            $pathGambar = null;
            if ($request->hasFile('gambar')) {
                $pathGambar = $request->file('gambar')->store('reward/' . $kodeReward, 'public');
            }

            $voucher->update([
                'kode_reward' => $kodeReward,
                'gambar' => $pathGambar,
            ]);

            $ukuranNames = $request->input('ukuran_names', []);
            $rasaNames = $request->input('rasa_names', []);
            $this->simpanTargetProduk($voucher, $validated['produk_ids'], $ukuranNames, $rasaNames);

            if ($validated['target_member'] === 'khusus' && !empty($validated['member_ids'])) {
                $voucher->members()->sync($validated['member_ids']);
            }

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

            $rewardVoucher->load(['produks', 'rewardProduks.detailProduk', 'rewardProduks.varianProduk']);

            $produks = Produk::with(['detailProduks', 'varianProduks'])
                ->where('status_aktif', true)
                ->orderBy('nama_produk', 'asc')
                ->get();
            $members = Member::orderBy('nama', 'asc')->get();
            $selectedProduks = $rewardVoucher->produks->pluck('id')->unique()->values()->toArray();
            $selectedMembers = $rewardVoucher->members->pluck('id')->toArray();

            $ukuranTerpilihPerProduk = [];
            $rasaTerpilihPerProduk = [];

            foreach ($rewardVoucher->rewardProduks as $rp) {
                $pid = $rp->produk_id;
                $uk = $rp->detailProduk?->ukuran ?: $rp->varianProduk?->ukuran;
                $rs = $rp->detailProduk?->variasi ?: $rp->varianProduk?->variasi;

                if ($uk) {
                    $ukuranTerpilihPerProduk[$pid][] = $uk;
                }
                if ($rs) {
                    $rasaTerpilihPerProduk[$pid][] = $rs;
                }
            }

            foreach ($ukuranTerpilihPerProduk as $pid => $list) {
                $ukuranTerpilihPerProduk[$pid] = array_values(array_unique($list));
            }
            foreach ($rasaTerpilihPerProduk as $pid => $list) {
                $rasaTerpilihPerProduk[$pid] = array_values(array_unique($list));
            }

            return view('admin.reward-voucher.edit', [
                'title' => 'Edit Reward Voucher',
                'voucher' => $rewardVoucher,
                'produks' => $this->dataProdukUntukForm($produks),
                'members' => $members,
                'selectedProduks' => $selectedProduks,
                'selectedMembers' => $selectedMembers,
                'ukuranTerpilihPerProduk' => $ukuranTerpilihPerProduk,
                'rasaTerpilihPerProduk' => $rasaTerpilihPerProduk,
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
                'status_aktif' => (bool) $request->boolean('status_aktif'),
                'target_member' => $validated['target_member'],
                'batas_penggunaan_per_member' => $validated['batas_penggunaan_per_member'] ?? 1,
                'jenis_potongan' => $validated['jenis_potongan'],
                'nominal_potongan' => $validated['jenis_potongan'] === 'nominal' ? $validated['nominal_potongan'] : 0,
                'persentase' => $validated['jenis_potongan'] === 'persentase' ? $validated['persentase'] : 0,
                'maksimal_potongan' => $validated['jenis_potongan'] === 'persentase' ? ($validated['maksimal_potongan'] ?? null) : null,
                'min_pembelian' => $validated['min_pembelian'] ?? 0,
            ];

            if ($request->hasFile('gambar')) {
                $kodeReward = $rewardVoucher->kode_reward;
                if ($kodeReward) {
                    Storage::disk('public')->deleteDirectory('reward/' . $kodeReward);
                }

                $dataUpdate['gambar'] = $request->file('gambar')->store('reward/' . $kodeReward, 'public');
            }

            $rewardVoucher->update($dataUpdate);

            $ukuranNames = $request->input('ukuran_names', []);
            $rasaNames = $request->input('rasa_names', []);
            $this->simpanTargetProduk($rewardVoucher, $validated['produk_ids'], $ukuranNames, $rasaNames);

            if ($validated['target_member'] === 'khusus') {
                $rewardVoucher->members()->sync($validated['member_ids'] ?? []);
            } else {
                $rewardVoucher->members()->detach();
            }

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

    public function tambahMember(Request $request, RewardKatalog $rewardVoucher)
    {
        try {
            abort_unless($rewardVoucher->jenis === 'voucher', 404);

            $validated = $request->validate([
                'member_ids' => 'required|array|min:1',
                'member_ids.*' => 'exists:members,id',
            ]);

            $rewardVoucher->members()->syncWithoutDetaching($validated['member_ids']);

            if ($rewardVoucher->target_member !== 'khusus') {
                $rewardVoucher->update(['target_member' => 'khusus']);
            }

            return back()->with('success', 'Member berhasil ditambahkan ke voucher.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function cabutMember(RewardKatalog $rewardVoucher, Member $member)
    {
        try {
            abort_unless($rewardVoucher->jenis === 'voucher', 404);

            $rewardVoucher->members()->detach($member->id);

            return back()->with('success', "Akses member {$member->nama} berhasil dicabut dari voucher ini.");
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

            if ($rewardVoucher->usages()->exists()) {
                return back()->with('error', 'Voucher ini sudah pernah digunakan dalam transaksi dan tidak bisa dihapus. Nonaktifkan saja agar riwayat penggunaan tetap tersimpan.');
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($rewardVoucher)
                ->withProperties($rewardVoucher->toArray())
                ->log('Menghapus reward voucher: ' . $rewardVoucher->nama);

            $kodeReward = $rewardVoucher->kode_reward;
            if ($kodeReward) {
                Storage::disk('public')->deleteDirectory('reward/' . $kodeReward);
            } elseif ($rewardVoucher->gambar && Storage::disk('public')->exists($rewardVoucher->gambar)) {
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
            'status_aktif' => 'nullable|boolean',
            'target_member' => 'required|in:semua,khusus,non_member',
            'member_ids' => 'required_if:target_member,khusus|array',
            'member_ids.*' => 'exists:members,id',
            'batas_penggunaan_per_member' => 'required|integer|min:1',
            'jenis_potongan' => 'required|in:nominal,persentase',
            'nominal_potongan' => 'required_if:jenis_potongan,nominal|nullable|numeric|min:0',
            'persentase' => 'required_if:jenis_potongan,persentase|nullable|numeric|min:0|max:100',
            'maksimal_potongan' => 'nullable|numeric|min:0',
            'min_pembelian' => 'nullable|numeric|min:0',
            'produk_ids' => 'required|array|min:1',
            'produk_ids.*' => 'exists:produks,id',
        ], [
            'nama.required' => 'Nama voucher wajib diisi.',
            'target_member.required' => 'Pilih target member voucher.',
            'target_member.in' => 'Target member voucher tidak valid.',
            'member_ids.required_if' => 'Pilih minimal satu member untuk voucher khusus.',
            'batas_penggunaan_per_member.required' => 'Batas penggunaan wajib diisi (minimal 1). Untuk voucher non member, angka ini adalah kuota total pemakaian.',
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'gambar.max' => 'Ukuran gambar maksimal 2MB.',
            'jenis_potongan.required' => 'Jenis potongan wajib dipilih.',
            'nominal_potongan.required_if' => 'Nominal potongan wajib diisi untuk voucher jenis nominal.',
            'persentase.required_if' => 'Persentase potongan wajib diisi untuk voucher jenis persentase.',
            'produk_ids.required' => 'Pilih minimal satu produk yang mendapat reward voucher ini.',
        ]);
    }

    private function dataProdukUntukForm(Collection $produks): Collection
    {
        $unikNama = fn ($c) => $c->filter()->map(fn ($x) => trim((string) $x))->filter()->unique()->values()->map(fn ($nama) => ['nama' => $nama]);

        return $produks->map(function (Produk $p) use ($unikNama) {
            if ($p->is_global && $p->jumlahVarianBersama() > 0) {
                $varianBersama = $p->varianBersamaAktifTersedia();
                $ukurans = $unikNama($varianBersama->pluck('ukuran'));
                $rasas = $unikNama($varianBersama->pluck('variasi'));

                return [
                    'id' => $p->id,
                    'nama_produk' => $p->nama_produk,
                    'kode_produk' => $p->kode_produk,
                    'foto' => $p->foto,
                    'harga_jual' => (float) $p->harga_jual,
                    'punya_ukuran' => $ukurans->isNotEmpty(),
                    'ukurans' => $ukurans,
                    'punya_varian_rasa' => $rasas->isNotEmpty(),
                    'rasas' => $rasas,
                ];
            }

            $detail = $p->detailProduks ?? collect();
            if ($detail->count() <= 1 && ! $detail->contains(fn ($d) => filled($d->ukuran) || filled($d->variasi))) {
                $ukurans = collect();
                $rasas = collect();
            } else {
                $ukurans = $unikNama($detail->pluck('ukuran'));
                $rasas = $unikNama($detail->pluck('variasi'));
            }

            return [
                'id' => $p->id,
                'nama_produk' => $p->nama_produk,
                'kode_produk' => $p->kode_produk,
                'foto' => $p->foto,
                'harga_jual' => (float) $p->harga_jual,
                'punya_ukuran' => $ukurans->isNotEmpty(),
                'ukurans' => $ukurans,
                'punya_varian_rasa' => $rasas->isNotEmpty(),
                'rasas' => $rasas,
            ];
        })->values();
    }

    private function resolveDetailIdsPerProduk(array $produkIds, array $ukuranPerProduk, array $rasaPerProduk): array
    {
        $produkIds = array_map('intval', $produkIds);
        $produks = Produk::with(['detailProduks', 'varianProduks'])->whereIn('id', $produkIds)->get()->keyBy('id');

        $norm = fn ($x) => mb_strtolower(trim((string) $x));
        $daftarNama = fn ($daftar) => collect(is_array($daftar) ? $daftar : [])
            ->map($norm)
            ->filter()
            ->values();

        $detailHasil = [];
        $varianHasil = [];
        foreach ($produkIds as $produkId) {
            $produk = $produks->get($produkId);
            if (!$produk) {
                continue;
            }

            $u = $daftarNama($ukuranPerProduk[$produkId] ?? []);
            $r = $daftarNama($rasaPerProduk[$produkId] ?? []);
            if ($u->isEmpty() && $r->isEmpty()) {
                continue;
            }

            if ($produk->is_global && $produk->jumlahVarianBersama() > 0) {
                $vIds = $produk->varianProduks
                    ->filter(fn ($v) => ($u->isEmpty() || $u->contains($norm($v->ukuran)))
                        && ($r->isEmpty() || $r->contains($norm($v->variasi))))
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                if (!empty($vIds)) {
                    $varianHasil[$produkId] = $vIds;
                }
            } else {
                $dIds = $produk->detailProduks
                    ->filter(fn ($d) => ($u->isEmpty() || $u->contains($norm($d->ukuran)))
                        && ($r->isEmpty() || $r->contains($norm($d->variasi))))
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                if (!empty($dIds)) {
                    $detailHasil[$produkId] = $dIds;
                }
            }
        }

        return [
            'detail_ids' => $detailHasil,
            'varian_ids' => $varianHasil,
        ];
    }

    private function simpanTargetProduk(RewardKatalog $voucher, array $produkIds, array $ukuranPerProduk, array $rasaPerProduk): void
    {
        RewardKatalogProduk::where('reward_katalog_id', $voucher->id)->delete();

        $target = $this->resolveDetailIdsPerProduk($produkIds, $ukuranPerProduk, $rasaPerProduk);
        $detailMap = $target['detail_ids'];
        $varianMap = $target['varian_ids'];

        foreach ($produkIds as $pId) {
            $pId = (int) $pId;
            $hasDetails = !empty($detailMap[$pId]);
            $hasVarians = !empty($varianMap[$pId]);

            if ($hasDetails) {
                foreach ($detailMap[$pId] as $dId) {
                    RewardKatalogProduk::create([
                        'reward_katalog_id' => $voucher->id,
                        'produk_id' => $pId,
                        'detail_produk_id' => $dId,
                        'varian_produk_id' => null,
                    ]);
                }
            } elseif ($hasVarians) {
                foreach ($varianMap[$pId] as $vId) {
                    RewardKatalogProduk::create([
                        'reward_katalog_id' => $voucher->id,
                        'produk_id' => $pId,
                        'detail_produk_id' => null,
                        'varian_produk_id' => $vId,
                    ]);
                }
            } else {
                RewardKatalogProduk::create([
                    'reward_katalog_id' => $voucher->id,
                    'produk_id' => $pId,
                    'detail_produk_id' => null,
                    'varian_produk_id' => null,
                ]);
            }
        }
    }
}