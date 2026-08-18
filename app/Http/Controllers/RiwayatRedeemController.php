<?php

namespace App\Http\Controllers;

use App\Models\PenukaranPoin;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiwayatRedeemController extends Controller
{
    /**
     * Daftar status yang boleh dipilih admin secara manual.
     * "menunggu_diambil" tidak termasuk karena itu status awal otomatis saat member redeem.
     */
    private const STATUS_BISA_DIUBAH = ['selesai', 'dibatalkan'];

    public function index(Request $request)
    {
        try {
            $search = $request->search;
            $status = $request->status;
            $jenis = $request->jenis;
            $tanggalMulai = $request->tanggal_mulai;
            $tanggalSelesai = $request->tanggal_selesai;

            $query = PenukaranPoin::with(['member', 'reward'])
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('kode_redeem', 'like', "%{$search}%")
                            ->orWhereHas('member', function ($m) use ($search) {
                                $m->where('nama', 'like', "%{$search}%")
                                    ->orWhere('no_hp', 'like', "%{$search}%");
                            })
                            ->orWhereHas('reward', function ($r) use ($search) {
                                $r->where('nama', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($jenis, function ($q) use ($jenis) {
                    $q->whereHas('reward', fn ($r) => $r->where('jenis', $jenis));
                })
                ->when($tanggalMulai, fn ($q) => $q->whereDate('created_at', '>=', $tanggalMulai))
                ->when($tanggalSelesai, fn ($q) => $q->whereDate('created_at', '<=', $tanggalSelesai));

            $riwayat = $query->latest()->paginate(10)->withQueryString();

            $ringkasan = [
                'total' => PenukaranPoin::count(),
                'menunggu_diambil' => PenukaranPoin::where('status', 'menunggu_diambil')->count(),
                'selesai' => PenukaranPoin::where('status', 'selesai')->count(),
                'dibatalkan' => PenukaranPoin::where('status', 'dibatalkan')->count(),
            ];

            return view('pages.riwayat-redeem.index', [
                'title' => 'Riwayat Redeem',
                'riwayat' => $riwayat,
                'ringkasan' => $ringkasan,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function updateStatus(Request $request, PenukaranPoin $riwayatRedeem)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:'.implode(',', self::STATUS_BISA_DIUBAH),
            ], [
                'status.required' => 'Status wajib dipilih.',
                'status.in' => 'Status tidak valid.',
            ]);

            DB::transaction(function () use ($validated, $riwayatRedeem) {
                $penukaran = PenukaranPoin::with('reward')
                    ->lockForUpdate()
                    ->findOrFail($riwayatRedeem->id);

                abort_unless($penukaran->status === 'menunggu_diambil', 422, 'Redeem ini sudah diproses sebelumnya dan tidak bisa diubah lagi.');

                if ($validated['status'] === 'selesai') {
                    $penukaran->update([
                        'status' => 'selesai',
                        'diambil_pada' => now(),
                    ]);

                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($penukaran)
                        ->log('Menandai redeem '.$penukaran->kode_redeem.' sebagai selesai diambil');

                    return;
                }

                $member = $penukaran->member()->lockForUpdate()->first();
                $member?->increment('poin', $penukaran->poin_terpakai);

                if ($penukaran->reward && ! is_null($penukaran->reward->stok)) {
                    $penukaran->reward->increment('stok');
                }

                $penukaran->update(['status' => 'dibatalkan']);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($penukaran)
                    ->log('Membatalkan redeem '.$penukaran->kode_redeem.', poin dikembalikan ke member');
            });

            return redirect()->back()->with('success', 'Status redeem berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}