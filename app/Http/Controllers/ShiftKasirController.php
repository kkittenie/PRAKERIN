<?php

namespace App\Http\Controllers;

use App\Exceptions\ShiftKasirException;
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
     *  - Shift sedang berjalan    : status shift + uang laci awal (laci akhir '?' sampai shift diselesaikan).
     *                               Total penjualan sengaja tidak ditampilkan ke kasir.
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

            return view('admin.shift-kasir.index', [
                'title' => 'Shift Kasir',
                'shift' => $shift,
                'saranModal' => $shift ? null : ShiftKasir::terakhirSelesai(),
                // Shift milik orang lain yang masih berjalan: selama ada, shift baru tidak bisa dibuka.
                'shiftLain' => $shift ? null : ShiftKasir::berjalanMilikLain($user->id),
                'riwayat' => $riwayat,
                'lewatHari' => $shift && $shift->tanggal->lt(now()->startOfDay()),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
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
        } catch (ShiftKasirException $e) {
            return redirect()->route('shift.index')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** Halaman selesai shift: input uang laci akhir + total penjualan hitungan kasir sendiri. */
    public function formTutup()
    {
        try {
            $shift = ShiftKasir::aktifUntuk(auth()->id());

            if (! $shift) {
                return redirect()->route('shift.index')->with('error', 'Tidak ada shift yang sedang berjalan.');
            }

            return view('admin.shift-kasir.tutup', [
                'title' => 'Selesai Shift',
                'shift' => $shift,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** Proses tutup shift. Dengan `keluar=1` kasir sekaligus logout. */
    public function tutup(Request $request)
    {
        try {
            $kolomNominal = ['uang_akhir', 'total_tunai', 'total_qris', 'total_debit', 'total_member_app'];
            $request->merge(collect($kolomNominal)
                ->mapWithKeys(fn ($k) => [$k => $this->bersihkanNominal($request->input($k))])
                ->all());

            $aturan = ['catatan' => 'nullable|string|max:255'];
            $pesanValidasi = ['catatan.max' => 'Catatan maksimal 255 karakter.'];
            $namaKolom = [
                'uang_akhir' => 'Sisa uang di laci',
                'total_tunai' => 'Total tunai',
                'total_qris' => 'Total QRIS',
                'total_debit' => 'Total debit',
                'total_member_app' => 'Total Member App',
            ];

            foreach ($namaKolom as $k => $label) {
                $aturan[$k] = 'required|numeric|min:0|max:999999999999';
                $pesanValidasi["{$k}.required"] = "{$label} wajib diisi (isi 0 kalau tidak ada).";
                $pesanValidasi["{$k}.numeric"] = "{$label} harus berupa angka.";
                $pesanValidasi["{$k}.min"] = "{$label} tidak boleh negatif.";
                $pesanValidasi["{$k}.max"] = "{$label} terlalu besar.";
            }

            $data = $request->validate($aturan, $pesanValidasi);

            $inputKasir = [
                'tunai' => (float) $data['total_tunai'],
                'qris' => (float) $data['total_qris'],
                'debit' => (float) $data['total_debit'],
                'member_app' => (float) $data['total_member_app'],
            ];

            $shift = DB::transaction(function () use ($data, $inputKasir) {
                // Kunci baris shift supaya klik ganda tidak menutup dua kali.
                $shift = ShiftKasir::berjalan()
                    ->where('user_id', auth()->id())
                    ->lockForUpdate()
                    ->latest('mulai_pada')
                    ->first();

                if (! $shift) {
                    throw new ShiftKasirException('Tidak ada shift yang sedang berjalan.');
                }

                return $shift->tutup((float) $data['uang_akhir'], $data['catatan'] ?? null, $inputKasir);
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
                    'input_kasir' => $inputKasir,
                ])
                ->log('menutup shift kasir');

            // Selisih sengaja tidak ditampilkan ke kasir (hanya terlihat di Rekap Shift).
            $pesan = $shift->labelShift() . ' selesai. Terima kasih.';

            if ($request->boolean('keluar')) {
                return app(AuthenticatedSessionController::class)->keluarkan($request)->with('success', $pesan);
            }

            return redirect()->route('shift.index')->with('success', $pesan);
        } catch (ShiftKasirException $e) {
            return redirect()->route('shift.index')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Tutup paksa shift yang sedang berjalan milik orang lain (untuk admin). Dipakai kalau kasirnya
     * lupa menutup shift atau tidak bisa login. Wajib mengisi alasan; uang laci akhir opsional.
     */
    public function tutupPaksa(Request $request, ShiftKasir $shift)
    {
        try {
            $request->merge(['uang_akhir' => $this->bersihkanNominal($request->input('uang_akhir'))]);

            $data = $request->validate([
                'uang_akhir' => 'nullable|numeric|min:0|max:999999999999',
                'alasan' => 'required|string|min:3|max:200',
            ], [
                'uang_akhir.numeric' => 'Sisa uang di laci harus berupa angka.',
                'uang_akhir.min' => 'Sisa uang di laci tidak boleh negatif.',
                'uang_akhir.max' => 'Sisa uang di laci terlalu besar.',
                'alasan.required' => 'Alasan tutup paksa wajib diisi.',
                'alasan.min' => 'Alasan tutup paksa terlalu pendek.',
                'alasan.max' => 'Alasan tutup paksa maksimal 200 karakter.',
            ]);

            $ditutup = DB::transaction(function () use ($shift, $data) {
                // Kunci baris shift supaya klik ganda, atau kasirnya menutup di saat yang sama, tidak menutup dua kali.
                $baris = ShiftKasir::berjalan()->whereKey($shift->id)->lockForUpdate()->first();

                if (! $baris) {
                    throw new ShiftKasirException('Shift ini sudah tidak berjalan (sudah ditutup).');
                }

                $uangAkhir = ($data['uang_akhir'] ?? '') === '' ? null : (float) $data['uang_akhir'];

                return $baris->tutupPaksa(auth()->user(), $uangAkhir, $data['alasan']);
            });

            activity()
                ->causedBy(auth()->user())
                ->performedOn($ditutup)
                ->withProperties([
                    'shift' => $ditutup->labelShift(),
                    'kasir' => $ditutup->nama_kasir,
                    'uang_akhir' => $ditutup->uang_akhir,
                    'uang_diharapkan' => $ditutup->uang_diharapkan,
                    'selisih' => $ditutup->selisih,
                    'alasan' => $data['alasan'],
                ])
                ->log('menutup paksa shift kasir');

            return redirect()->route('rekap-shift.show', $ditutup)
                ->with('success', $ditutup->labelShift() . ' milik ' . $ditutup->nama_kasir . ' ditutup paksa.');
        } catch (ShiftKasirException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /** "1.250.000" / "Rp 1.250.000" -> "1250000" supaya lolos validasi numeric. */
    private function bersihkanNominal($nilai): string
    {
        return preg_replace('/[^\d]/', '', (string) $nilai) ?? '';
    }
}