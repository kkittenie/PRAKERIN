<?php

namespace App\Http\Controllers;

use App\Models\ShiftKasir;
use App\Models\Transaksi;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapShiftController extends Controller
{
    /** Pilihan filter role: hanya role yang bisa membuka shift (Pegawai tidak termasuk). */
    private const ROLE_FILTER = ['Admin', 'Kasir'];

    /** Query dasar + filter (dipakai halaman rekap dan ekspor Excel). */
    /** Role yang dipilih di filter (null kalau kosong atau tidak dikenal). */
    private function roleDipilih(Request $request): ?Role
    {
        $nama = $request->input('role');

        return in_array($nama, self::ROLE_FILTER, true) ? Role::where('name', $nama)->first() : null;
    }

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

        if ($role = $this->roleDipilih($request)) {
            // Role dibaca dari akun saat ini (role tidak disimpan di data shift).
            $query->whereHas('user', fn ($q) => $q->role($role));
        }

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

            $roleDipilih = $this->roleDipilih($request);

            $kasirList = ShiftKasir::query()
                ->whereNotNull('user_id')
                ->when($roleDipilih, fn ($q) => $q->whereHas('user', fn ($u) => $u->role($roleDipilih)))
                ->select('user_id', 'nama_kasir')
                ->distinct()
                ->orderBy('nama_kasir')
                ->get();

            return view('pages.rekap-shift.index', [
                'title' => 'Rekap Shift',
                'daftar' => $daftar,
                'total' => $total,
                'kasirList' => $kasirList,
                'roleList' => self::ROLE_FILTER,
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

    /** Ekspor rekap (sesuai filter) ke file Excel (.xlsx) yang sudah dirapikan. */
    public function export(Request $request)
    {
        try {
            [$query, $mulai, $selesai] = $this->buatQuery($request);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekap Shift');

            $lastCol = 'U';
            $uang = '#,##0';
            $waktu = 'dd/mm/yyyy hh:mm';

            // Judul & periode
            $sheet->setCellValue('A1', 'Rekap Shift Kasir');
            $sheet->setCellValue('A2', 'Periode: ' . \Carbon\Carbon::parse($mulai)->format('d/m/Y')
                . ' s/d ' . \Carbon\Carbon::parse($selesai)->format('d/m/Y'));
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('666666');

            // Header dua baris: baris 4 = kelompok, baris 5 = nama kolom
            $headerAtas = 4;
            $headerBawah = 5;
            $kolom = [
                'A' => 'Tanggal', 'B' => 'Shift', 'C' => 'Kasir', 'D' => 'Mulai', 'E' => 'Selesai', 'F' => 'Status',
                'G' => 'Uang Laci Awal',
                'H' => 'Jumlah', 'I' => 'Nominal',
                'J' => 'Jumlah', 'K' => 'Nominal',
                'L' => 'Jumlah', 'M' => 'Nominal',
                'N' => 'Total Transaksi', 'O' => 'Total Penjualan',
                'P' => 'Uang Seharusnya di Laci', 'Q' => 'Uang Laci Akhir', 'R' => 'Selisih', 'S' => 'Keterangan Selisih',
                'T' => 'Catatan Mulai', 'U' => 'Catatan Selesai',
            ];
            foreach ($kolom as $huruf => $judul) {
                $sheet->setCellValue("{$huruf}{$headerBawah}", $judul);
            }
            // Kolom tanpa kelompok: gabung baris 4-5 supaya judulnya satu sel
            foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U'] as $huruf) {
                $sheet->setCellValue("{$huruf}{$headerAtas}", $kolom[$huruf]);
                $sheet->mergeCells("{$huruf}{$headerAtas}:{$huruf}{$headerBawah}");
            }
            foreach (['H' => 'Tunai', 'J' => 'QRIS', 'L' => 'Debit'] as $huruf => $judul) {
                $sheet->setCellValue("{$huruf}{$headerAtas}", $judul);
                $sheet->mergeCells("{$huruf}{$headerAtas}:" . chr(ord($huruf) + 1) . $headerAtas);
            }

            $header = $sheet->getStyle("A{$headerAtas}:{$lastCol}{$headerBawah}");
            $header->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $header->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B45309');
            $header->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getRowDimension($headerBawah)->setRowHeight(30);

            // Data
            $baris = $headerBawah + 1;
            $awalData = $baris;

            (clone $query)->orderBy('tanggal')->orderBy('nomor_shift')->orderBy('id')
                ->chunk(200, function ($rows) use ($sheet, &$baris) {
                    foreach ($rows as $s) {
                        $r = $s->ringkasan();

                        $sheet->setCellValue("A{$baris}", ExcelDate::PHPToExcel($s->tanggal->copy()->startOfDay()));
                        $sheet->setCellValue("B{$baris}", $s->nomor_shift);
                        $sheet->setCellValue("C{$baris}", $s->nama_kasir);
                        if ($s->mulai_pada) {
                            $sheet->setCellValue("D{$baris}", ExcelDate::PHPToExcel($s->mulai_pada));
                        }
                        if ($s->selesai_pada) {
                            $sheet->setCellValue("E{$baris}", ExcelDate::PHPToExcel($s->selesai_pada));
                        }
                        $sheet->setCellValue("F{$baris}", $s->sedangBerjalan() ? 'Berjalan' : 'Selesai');
                        $sheet->setCellValue("G{$baris}", round($s->modal_awal));
                        $sheet->setCellValue("H{$baris}", $r['tunai']['jumlah']);
                        $sheet->setCellValue("I{$baris}", round($r['tunai']['nominal']));
                        $sheet->setCellValue("J{$baris}", $r['qris']['jumlah']);
                        $sheet->setCellValue("K{$baris}", round($r['qris']['nominal']));
                        $sheet->setCellValue("L{$baris}", $r['debit']['jumlah']);
                        $sheet->setCellValue("M{$baris}", round($r['debit']['nominal']));
                        $sheet->setCellValue("N{$baris}", $r['jumlah_transaksi']);
                        $sheet->setCellValue("O{$baris}", round($r['total_penjualan']));
                        $sheet->setCellValue("P{$baris}", round($r['uang_diharapkan']));
                        if ($s->uang_akhir !== null) {
                            $sheet->setCellValue("Q{$baris}", round($s->uang_akhir));
                        }
                        if ($s->selisih !== null) {
                            $sheet->setCellValue("R{$baris}", round($s->selisih));
                        }
                        $label = $s->labelSelisih();
                        $sheet->setCellValue("S{$baris}", $label ?? '-');
                        if ($label === 'Kurang') {
                            $sheet->getStyle("R{$baris}:S{$baris}")->getFont()->getColor()->setRGB('DC2626');
                        } elseif ($label === 'Lebih') {
                            $sheet->getStyle("R{$baris}:S{$baris}")->getFont()->getColor()->setRGB('B45309');
                        }
                        $sheet->setCellValue("T{$baris}", $s->catatan_mulai);
                        $sheet->setCellValue("U{$baris}", $s->catatan_tutup);

                        $baris++;
                    }
                });

            $akhirData = $baris - 1;

            $adaData = $akhirData >= $awalData;

            if (! $adaData) {
                $sheet->setCellValue("A{$awalData}", 'Tidak ada data shift pada periode ini.');
                $sheet->mergeCells("A{$awalData}:{$lastCol}{$awalData}");
                $sheet->getStyle("A{$awalData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $akhirData = $awalData;
            } else {
                // Baris total: hanya shift berstatus Selesai (sama seperti ringkasan di halaman Rekap Shift)
                $baris = $akhirData + 1;
                $sheet->setCellValue("A{$baris}", 'TOTAL (shift selesai)');
                $sheet->mergeCells("A{$baris}:F{$baris}");
                // Uang laci awal/seharusnya/akhir TIDAK dijumlahkan: itu saldo yang terbawa
                // dari shift ke shift, jadi kolom G, P, Q dikosongkan di baris total.
                foreach (['H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'R'] as $huruf) {
                    $sheet->setCellValue(
                        "{$huruf}{$baris}",
                        "=SUMIF(\$F\${$awalData}:\$F\${$akhirData},\"Selesai\",{$huruf}{$awalData}:{$huruf}{$akhirData})"
                    );
                }
                $total = $sheet->getStyle("A{$baris}:{$lastCol}{$baris}");
                $total->getFont()->setBold(true);
                $total->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
                $sheet->getStyle("A{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $akhirTabel = $baris;
            }
            $akhirTabel = $akhirTabel ?? $akhirData;

            // Format angka & tanggal
            $sheet->getStyle("A{$awalData}:A{$akhirData}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $sheet->getStyle("D{$awalData}:E{$akhirData}")->getNumberFormat()->setFormatCode($waktu);
            $sheet->getStyle("G{$awalData}:R{$akhirTabel}")->getNumberFormat()->setFormatCode($uang);

            // Perataan: teks rata kiri, angka rata kanan, sisanya di tengah
            $sheet->getStyle("A{$awalData}:{$lastCol}{$akhirTabel}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$awalData}:B{$akhirData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$awalData}:F{$akhirData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("S{$awalData}:S{$akhirData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$awalData}:R{$akhirTabel}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("C{$awalData}:C{$akhirData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("T{$awalData}:U{$akhirData}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

            // Border tipis seluruh tabel
            $sheet->getStyle("A{$headerAtas}:{$lastCol}{$akhirTabel}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');

            // Lebar kolom tetap (lebih stabil daripada auto-size untuk angka & tanggal)
            $lebar = [
                'A' => 12, 'B' => 8, 'C' => 20, 'D' => 17, 'E' => 17, 'F' => 11, 'G' => 15,
                'H' => 8, 'I' => 14, 'J' => 8, 'K' => 14, 'L' => 8, 'M' => 14,
                'N' => 12, 'O' => 16, 'P' => 18, 'Q' => 16, 'R' => 13, 'S' => 14, 'T' => 30, 'U' => 30,
            ];
            foreach ($lebar as $huruf => $w) {
                $sheet->getColumnDimension($huruf)->setWidth($w);
            }

            // Bekukan header + 3 kolom pertama, filter di baris nama kolom, siap cetak landscape
            $sheet->freezePane('D' . $awalData);
            if ($adaData) {
                $sheet->setAutoFilter("A{$headerBawah}:{$lastCol}{$akhirData}");
            }
            $sheet->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                ->setFitToWidth(1)
                ->setFitToHeight(0);
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerAtas, $headerBawah);
            $sheet->getSheetView()->setZoomScale(90);

            $namaFile = "rekap-shift-{$mulai}_sd_{$selesai}.xlsx";

            return response()->streamDownload(function () use ($spreadsheet) {
                (new Xlsx($spreadsheet))->save('php://output');
            }, $namaFile, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}