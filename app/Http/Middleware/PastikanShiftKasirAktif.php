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
            return response()->json([
                'status' => 'error',
                'success' => false,
                'pesan' => 'Shift kasir belum dibuka. Buka shift dulu di menu Shift Kasir.',
                'message' => 'Shift kasir belum dibuka. Buka shift dulu di menu Shift Kasir.',
                'redirect' => route('shift.index'),
            ], 423);
        }

        return $next($request);
    }
}