<?php

namespace App\Http\Controllers;

use App\Models\PenukaranPoin;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiwayatRedeemController extends Controller
{
    private const STATUS_BISA_DIUBAH = ['selesai', 'dibatalkan'];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $status = $request->status;
                $jenis = $request->jenis;
                $tanggalMulai = $request->tanggal_mulai;
                $tanggalSelesai = $request->tanggal_selesai;

                $query = PenukaranPoin::with(['member', 'reward'])
                    ->when($status, fn ($q) => $q->where('status', $status))
                    ->when($jenis, function ($q) use ($jenis) {
                        $q->whereHas('reward', fn ($r) => $r->where('jenis', $jenis));
                    })
                    ->when($tanggalMulai, fn ($q) => $q->whereDate('created_at', '>=', $tanggalMulai))
                    ->when($tanggalSelesai, fn ($q) => $q->whereDate('created_at', '<=', $tanggalSelesai));

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->filterColumn('kode_redeem', function ($q, $keyword) {
                        $q->where(function ($sub) use ($keyword) {
                            $sub->where('kode_redeem', 'like', "%{$keyword}%")
                                ->orWhereHas('member', function ($m) use ($keyword) {
                                    $m->where('nama', 'like', "%{$keyword}%")
                                        ->orWhere('no_hp', 'like', "%{$keyword}%");
                                })
                                ->orWhereHas('reward', function ($r) use ($keyword) {
                                    $r->where('nama', 'like', "%{$keyword}%");
                                });
                        });
                    })
                    ->addColumn('kode_html', fn ($item) => view('pages.riwayat-redeem.partials.kode', compact('item'))->render())
                    ->addColumn('member_html', fn ($item) => view('pages.riwayat-redeem.partials.member', compact('item'))->render())
                    ->addColumn('reward_html', fn ($item) => view('pages.riwayat-redeem.partials.reward', compact('item'))->render())
                    ->editColumn('poin_terpakai', fn ($item) => '-' . number_format($item->poin_terpakai, 0, ',', '.'))
                    ->editColumn('created_at', fn ($item) => $item->created_at->translatedFormat('d M Y, H:i'))
                    ->addColumn('status_html', fn ($item) => view('pages.riwayat-redeem.partials.status', compact('item'))->render())
                    ->orderColumn('member.nama', function ($query, $order) {
                        $query->leftJoin('members', 'penukaran_poin.member_id', '=', 'members.id')
                            ->orderBy('members.nama', $order)
                            ->select('penukaran_poin.*');
                    })
                    ->orderColumn('reward.nama', function ($query, $order) {
                        $query->leftJoin('reward_katalog', 'penukaran_poin.reward_katalog_id', '=', 'reward_katalog.id')
                            ->orderBy('reward_katalog.nama', $order)
                            ->select('penukaran_poin.*');
                    })
                    ->rawColumns(['kode_html', 'member_html', 'reward_html', 'status_html'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data riwayat redeem.'], 500);
            }
        }

        $ringkasan = [
            'total' => PenukaranPoin::count(),
            'menunggu_diambil' => PenukaranPoin::where('status', 'menunggu_diambil')->count(),
            'selesai' => PenukaranPoin::where('status', 'selesai')->count(),
            'dibatalkan' => PenukaranPoin::where('status', 'dibatalkan')->count(),
        ];

        return view('pages.riwayat-redeem.index', [
            'title' => 'Riwayat Redeem',
            'ringkasan' => $ringkasan,
        ]);
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