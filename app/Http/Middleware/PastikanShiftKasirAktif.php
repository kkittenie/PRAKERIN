<?php

namespace App\Http\Middleware;

use App\Models\ShiftKasir;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pembayaran di kasir hanya boleh diproses kalau kasirnya sudah membuka shift.
 * Hanya berlaku untuk user yang punya permission `tambah_shift_kasir`; role yang belum
 * diatur untuk fitur shift tetap bisa memakai kasir seperti biasa.
 */
class PastikanShiftKasirAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->can('tambah_shift_kasir') && ! ShiftKasir::aktifUntuk($user->id)) {
            // Hanya satu shift yang boleh berjalan: kalau laci sedang dipegang orang lain, jelaskan.
            $lain = ShiftKasir::berjalanMilikLain($user->id);
            $pesan = $lain
                ? $lain->labelShift() . ' milik ' . $lain->nama_kasir . ' masih berjalan. Tunggu sampai shift itu ditutup, lalu buka shift Anda di menu Shift Kasir.'
                : 'Shift kasir belum dibuka. Buka shift dulu di menu Shift Kasir.';

            return response()->json([
                'status' => 'error',
                'success' => false,
                'pesan' => $pesan,
                'message' => $pesan,
                'redirect' => route('shift.index'),
            ], 423);
        }

        return $next($request);
    }
}