<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\OtpVerification;
use Illuminate\Http\Request;

class MemberOtpController extends Controller
{
    /**
     * Generate dan simpan OTP
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string|max:20',
        ]);

        $noHp = $request->no_hp;

        // Cek apakah nomor HP sudah terdaftar
        $member = Member::where('no_hp', $noHp)->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP belum terdaftar sebagai member.',
            ], 404);
        }

        // Generate OTP 6 digit
        $otp = str_pad(
            random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        // Hapus OTP lama
        OtpVerification::where('no_hp', $noHp)
            ->where('is_used', false)
            ->delete();

        // Simpan OTP ke database
        OtpVerification::create([
            'no_hp' => $noHp,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dibuat. Silakan cek kode OTP di database.',
        ]);
    }


    /**
     * Verifikasi OTP dan login member
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);

        $noHp = $request->no_hp;
        $otp = $request->otp;

        // Cari OTP
        $otpVerification = OtpVerification::where('no_hp', $noHp)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otpVerification) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah.',
            ], 422);
        }

        // Cek expired
        if ($otpVerification->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah expired. Silakan minta OTP baru.',
            ], 422);
        }

        // Cari member
        $member = Member::where('no_hp', $noHp)->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan.',
            ], 404);
        }

        // Tandai OTP sudah digunakan
        $otpVerification->update([
            'is_used' => true,
        ]);

        // Simpan ID member ke session
        $request->session()->put('member_id', $member->id);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'member' => [
                'id' => $member->id,
                'nama' => $member->nama,
                'no_hp' => $member->no_hp,
            ],
        ]);
    }


    /**
     * Logout member
     */
    public function logout(Request $request)
    {
        $request->session()->forget('member_id');

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}