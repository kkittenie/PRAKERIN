<?php

namespace App\Http\Controllers;

use App\Models\ShiftKasir;
use App\Models\Transaksi;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class RekapShiftController extends Controller
{
    /** Query dasar + filter (dipakai halaman rekap dan ekspor CSV). */
    private function buatQuery(Request $request)
    {
        $mulai = $request->input('tanggal_mulai', now()->startOfMonth()->toDateString());
        $selesai = $request->input('tanggal_selesai', now()->toDateString());

        if ($selesai < $mulai) {
            $selesai = $mulai;
        }

        $query = ShiftKasir::query()
            ->whereDate('tanggal', '>=', $mulai)
            ->whereDate('tanggal', '<=', $selesai);

        if ($request->filled('kasir')) {
            $query->where('user_id', $request->input('kasir'));
        }

        if (in_array($request->input('status'), [ShiftKasir::STATUS_BERJALAN, ShiftKasir::STATUS_SELESAI], true)) {
            $query->where('status', $request->input('status'));
        }

        return [$query, $mulai, $selesai];
    }

    public function index(Request $request)
    {
        try {
            [$query, $mulai, $selesai] = $this->buatQuery($request);

            // Ringkasan keseluruhan dari shift yang sudah selesai (angkanya sudah dibekukan).
            $total = (clone $query)->selesai()->selectRaw('
                COUNT(*) as jumlah_shift,
                COALESCE(SUM(modal_awal), 0) as modal_awal,
                COALESCE(SUM(total_tunai), 0) as tunai,
                COALESCE(SUM(total_qris), 0) as qris,
                COALESCE(SUM(total_debit), 0) as debit,
                COALESCE(SUM(total_lainnya), 0) as lainnya,
                COALESCE(SUM(jumlah_transaksi), 0) as transaksi,
                COALESCE(SUM(selisih), 0) as selisih
            ')->first();

            $daftar = (clone $query)
                ->with('user')
                ->orderByDesc('tanggal')
                ->orderByDesc('nomor_shift')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();

            $kasirList = ShiftKasir::query()
                ->whereNotNull('user_id')
                ->select('user_id', 'nama_kasir')
                ->distinct()
                ->orderBy('nama_kasir')
                ->get();

            return view('pages.rekap-shift.index', [
                'title' => 'Rekap Shift',
                'daftar' => $daftar,
                'total' => $total,
                'kasirList' => $kasirList,
                'tanggalMulai' => $mulai,
                'tanggalSelesai' => $selesai,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(ShiftKasir $shift)
    {
        try {
            $shift->load('user');

            $transaksi = Transaksi::penjualanSah()
                ->where('shift_kasir_id', $shift->id)
                ->orderBy('created_at')
                ->paginate(25)
                ->withQueryString();

            return view('pages.rekap-shift.show', [
                'title' => 'Detail ' . $shift->labelShift(),
                'shift' => $shift,
                'ringkasan' => $shift->ringkasan(),
                'transaksi' => $transaksi,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** Ekspor rekap (sesuai filter) ke CSV yang bisa dibuka di Excel. */
    public function export(Request $request)
    {
        try {
            [$query, $mulai, $selesai] = $this->buatQuery($request);

            $namaFile = "rekap-shift-{$mulai}_sd_{$selesai}.csv";

            return response()->streamDownload(function () use ($query) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8

                fputcsv($out, [
                    'Tanggal', 'Shift', 'Kasir', 'Mulai', 'Selesai', 'Status',
                    'Uang Laci Awal',
                    'Jml Tunai', 'Nominal Tunai',
                    'Jml QRIS', 'Nominal QRIS',
                    'Jml Debit', 'Nominal Debit',
                    'Jml Lainnya', 'Nominal Lainnya',
                    'Total Transaksi', 'Total Penjualan',
                    'Uang Seharusnya di Laci', 'Uang Laci Akhir', 'Selisih', 'Keterangan Selisih',
                    'Catatan Mulai', 'Catatan Selesai',
                ], ';');

                (clone $query)->orderBy('tanggal')->orderBy('nomor_shift')->orderBy('id')
                    ->chunk(200, function ($rows) use ($out) {
                        foreach ($rows as $s) {
                            $r = $s->ringkasan();

                            fputcsv($out, [
                                $s->tanggal->format('Y-m-d'),
                                $s->nomor_shift,
                                $s->nama_kasir,
                                optional($s->mulai_pada)->format('Y-m-d H:i'),
                                optional($s->selesai_pada)->format('Y-m-d H:i'),
                                $s->sedangBerjalan() ? 'Berjalan' : 'Selesai',
                                round($s->modal_awal),
                                $r['tunai']['jumlah'], round($r['tunai']['nominal']),
                                $r['qris']['jumlah'], round($r['qris']['nominal']),
                                $r['debit']['jumlah'], round($r['debit']['nominal']),
                                $r['lainnya']['jumlah'], round($r['lainnya']['nominal']),
                                $r['jumlah_transaksi'], round($r['total_penjualan']),
                                round($r['uang_diharapkan']),
                                $s->uang_akhir !== null ? round($s->uang_akhir) : '',
                                $s->selisih !== null ? round($s->selisih) : '',
                                $s->labelSelisih() ?? '',
                                $s->catatan_mulai,
                                $s->catatan_tutup,
                            ], ';');
                        }
                    });

                fclose($out);
            }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}