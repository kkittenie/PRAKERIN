<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\ShiftKasir;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftKasirController extends Controller
{
    /**
     * Halaman Shift Kasir.
     *  - Belum ada shift berjalan : form buka shift (input uang laci awal)
     *  - Shift sedang berjalan    : ringkasan pembayaran (laci, tunai, QRIS, debit) yang update otomatis
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $shift = ShiftKasir::aktifUntuk($user->id);

            $riwayat = ShiftKasir::selesai()
                ->where('user_id', $user->id)
                ->latest('selesai_pada')
                ->limit(5)
                ->get();

            return view('pages.shift-kasir.index', [
                'title' => 'Shift Kasir',
                'shift' => $shift,
                'ringkasan' => $shift?->ringkasan(),
                'saranModal' => $shift ? null : ShiftKasir::terakhirSelesai(),
                'riwayat' => $riwayat,
                'lewatHari' => $shift && $shift->tanggal->lt(now()->startOfDay()),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** JSON ringkasan shift berjalan (dipanggil berkala oleh halaman Shift Kasir). */
    public function ringkasan()
    {
        try {
            $shift = ShiftKasir::aktifUntuk(auth()->id());

            if (! $shift) {
                return response()->json(['status' => 'tidak_aktif'], 200);
            }

            return response()->json([
                'status' => 'success',
                'ringkasan' => $shift->ringkasan(),
                'diperbarui' => now()->format('H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error'], 500);
        }
    }

    /** Proses buka shift. */
    public function mulai(Request $request)
    {
        try {
            $request->merge(['modal_awal' => $this->bersihkanNominal($request->input('modal_awal'))]);

            $data = $request->validate([
                'modal_awal' => 'required|numeric|min:0|max:999999999999',
                'catatan' => 'nullable|string|max:255',
            ], [
                'modal_awal.required' => 'Nominal uang di laci wajib diisi.',
                'modal_awal.numeric' => 'Nominal uang di laci harus berupa angka.',
                'modal_awal.min' => 'Nominal uang di laci tidak boleh negatif.',
                'modal_awal.max' => 'Nominal uang di laci terlalu besar.',
                'catatan.max' => 'Catatan maksimal 255 karakter.',
            ]);

            $shift = ShiftKasir::mulai(auth()->user(), (float) $data['modal_awal'], $data['catatan'] ?? null);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($shift)
                ->withProperties([
                    'shift' => $shift->labelShift(),
                    'modal_awal' => $shift->modal_awal,
                ])
                ->log('membuka shift kasir');

            $tujuan = auth()->user()->can('lihat_transaksi') ? route('transaksi.indeks') : route('shift.index');

            return redirect($tujuan)->with('success', $shift->labelShift() . ' dimulai dengan uang laci Rp ' . number_format($shift->modal_awal, 0, ',', '.') . '.');
        } catch (\RuntimeException $e) {
            return redirect()->route('shift.index')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** Halaman selesai shift: input uang laci akhir. */
    public function formTutup()
    {
        try {
            $shift = ShiftKasir::aktifUntuk(auth()->id());

            if (! $shift) {
                return redirect()->route('shift.index')->with('error', 'Tidak ada shift yang sedang berjalan.');
            }

            return view('pages.shift-kasir.tutup', [
                'title' => 'Selesai Shift',
                'shift' => $shift,
                'ringkasan' => $shift->ringkasan(),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** Proses tutup shift. Dengan `keluar=1` kasir sekaligus logout. */
    public function tutup(Request $request)
    {
        try {
            $request->merge(['uang_akhir' => $this->bersihkanNominal($request->input('uang_akhir'))]);

            $data = $request->validate([
                'uang_akhir' => 'required|numeric|min:0|max:999999999999',
                'catatan' => 'nullable|string|max:255',
            ], [
                'uang_akhir.required' => 'Sisa uang di laci wajib diisi.',
                'uang_akhir.numeric' => 'Sisa uang di laci harus berupa angka.',
                'uang_akhir.min' => 'Sisa uang di laci tidak boleh negatif.',
                'uang_akhir.max' => 'Sisa uang di laci terlalu besar.',
                'catatan.max' => 'Catatan maksimal 255 karakter.',
            ]);

            $shift = DB::transaction(function () use ($data) {
                // Kunci baris shift supaya klik ganda tidak menutup dua kali.
                $shift = ShiftKasir::berjalan()
                    ->where('user_id', auth()->id())
                    ->lockForUpdate()
                    ->latest('mulai_pada')
                    ->first();

                if (! $shift) {
                    throw new \RuntimeException('Tidak ada shift yang sedang berjalan.');
                }

                return $shift->tutup((float) $data['uang_akhir'], $data['catatan'] ?? null);
            });

            activity()
                ->causedBy(auth()->user())
                ->performedOn($shift)
                ->withProperties([
                    'shift' => $shift->labelShift(),
                    'modal_awal' => $shift->modal_awal,
                    'uang_akhir' => $shift->uang_akhir,
                    'uang_diharapkan' => $shift->uang_diharapkan,
                    'selisih' => $shift->selisih,
                ])
                ->log('menutup shift kasir');

            $pesan = $shift->labelShift() . ' selesai. Selisih laci: ' . $this->formatSelisih($shift->selisih) . '.';

            if ($request->boolean('keluar')) {
                return app(AuthenticatedSessionController::class)->keluarkan($request)->with('success', $pesan);
            }

            return redirect()->route('shift.index')->with('success', $pesan);
        } catch (\RuntimeException $e) {
            return redirect()->route('shift.index')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** "1.250.000" / "Rp 1.250.000" -> "1250000" supaya lolos validasi numeric. */
    private function bersihkanNominal($nilai): string
    {
        return preg_replace('/[^\d]/', '', (string) $nilai) ?? '';
    }

    private function formatSelisih(?float $selisih): string
    {
        $selisih = (float) $selisih;

        if (abs($selisih) < 0.5) {
            return 'sesuai (Rp 0)';
        }

        return ($selisih > 0 ? 'lebih Rp ' : 'kurang Rp ') . number_format(abs($selisih), 0, ',', '.');
    }
}