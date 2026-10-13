<?php

namespace App\Http\Controllers;

use App\Models\PenukaranPoin;
use App\Models\VoucherUsage;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiwayatRedeemController extends Controller
{
    private const STATUS_BISA_DIUBAH = ['selesai', 'dibatalkan'];

    public function index(Request $request)
    {
        $tipe = $request->query('tipe', 'voucher'); // 'voucher' atau 'produk'

        if ($request->ajax()) {
            try {
                if ($tipe === 'voucher') {
                    $tanggalMulai = $request->tanggal_mulai;
                    $tanggalSelesai = $request->tanggal_selesai;

                    $query = VoucherUsage::with(['voucher', 'member', 'transaksi'])
                        ->when($tanggalMulai, fn ($q) => $q->whereDate('used_at', '>=', $tanggalMulai))
                        ->when($tanggalSelesai, fn ($q) => $q->whereDate('used_at', '<=', $tanggalSelesai))
                        ->latest('used_at');

                    return \Yajra\DataTables\Facades\DataTables::of($query)
                        ->addIndexColumn()
                        ->filterColumn('voucher_nama', function ($q, $keyword) {
                            $q->where(function ($sub) use ($keyword) {
                                $sub->whereHas('voucher', fn ($v) => $v->where('nama', 'like', "%{$keyword}%"))
                                    ->orWhereHas('member', fn ($m) => $m->where('nama', 'like', "%{$keyword}%")->orWhere('no_hp', 'like', "%{$keyword}%"))
                                    ->orWhereHas('transaksi', fn ($t) => $t->where('kode_transaksi', 'like', "%{$keyword}%"));
                            });
                        })
                        ->addColumn('voucher_html', function ($item) {
                            $potongan = '';
                            if ($item->voucher) {
                                $potongan = $item->voucher->jenis_potongan === 'nominal'
                                    ? 'Rp ' . number_format($item->voucher->nominal_potongan, 0, ',', '.')
                                    : $item->voucher->persentase . '%';
                            }
                            return '<div class="font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">' . e($item->voucher->nama ?? 'Voucher') . '</div>'
                                . '<div class="text-[11px] text-rose-600 dark:text-rose-400 font-medium">Potongan: ' . $potongan . '</div>';
                        })
                        ->addColumn('member_html', function ($item) {
                            return '<div class="font-medium text-gray-800 dark:text-gray-200 text-xs sm:text-sm">' . e($item->member->nama ?? 'Non-member') . '</div>'
                                . '<div class="text-[11px] text-gray-400">' . e($item->member->no_hp ?? '-') . '</div>';
                        })
                        ->addColumn('transaksi_html', function ($item) {
                            if (!$item->transaksi) return '<span class="text-xs text-gray-400">-</span>';
                            return '<div class="font-mono text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded-md inline-block">' . e($item->transaksi->kode_transaksi) . '</div>'
                                . '<div class="text-[11px] text-gray-500 mt-0.5">Total: Rp ' . number_format($item->transaksi->total_bayar, 0, ',', '.') . '</div>';
                        })
                        ->addColumn('used_at_html', function ($item) {
                            return '<span class="text-xs text-gray-600 dark:text-gray-300">' . ($item->used_at ? $item->used_at->translatedFormat('d M Y, H:i') : '-') . '</span>';
                        })
                        ->rawColumns(['voucher_html', 'member_html', 'transaksi_html', 'used_at_html'])
                        ->make(true);
                }

                // Tipe reward non-voucher (misal reward produk fisik dengan poin)
                $status = $request->status;
                $tanggalMulai = $request->tanggal_mulai;
                $tanggalSelesai = $request->tanggal_selesai;

                $query = PenukaranPoin::with(['member', 'reward'])
                    ->whereHas('reward', fn ($r) => $r->where('jenis', '!=', 'voucher'))
                    ->when($status, fn ($q) => $q->where('status', $status))
                    ->when($tanggalMulai, fn ($q) => $q->whereDate('created_at', '>=', $tanggalMulai))
                    ->when($tanggalSelesai, fn ($q) => $q->whereDate('created_at', '<=', $tanggalSelesai))
                    ->latest('created_at');

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
                    ->rawColumns(['kode_html', 'member_html', 'reward_html', 'status_html'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data.'], 500);
            }
        }

        $ringkasan = [
            'total_voucher' => VoucherUsage::count(),
            'total_produk' => PenukaranPoin::whereHas('reward', fn ($r) => $r->where('jenis', '!=', 'voucher'))->count(),
            'menunggu_diambil' => PenukaranPoin::whereHas('reward', fn ($r) => $r->where('jenis', '!=', 'voucher'))->where('status', 'menunggu_diambil')->count(),
            'selesai' => PenukaranPoin::whereHas('reward', fn ($r) => $r->where('jenis', '!=', 'voucher'))->where('status', 'selesai')->count(),
        ];

        return view('pages.riwayat-redeem.index', [
            'title' => 'Riwayat Penggunaan Voucher & Reward',
            'ringkasan' => $ringkasan,
            'tipeAktif' => $tipe,
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