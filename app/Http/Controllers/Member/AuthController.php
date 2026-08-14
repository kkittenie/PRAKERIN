<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Normalisasi nomor HP ke format +62xxxx
     */
    private function normalizeNoHp(string $noHp): string
    {
        $noHp = trim($noHp);

        if (str_starts_with($noHp, '0')) {
            return '+62' . substr($noHp, 1);
        }

        if (!str_starts_with($noHp, '+')) {
            return '+62' . $noHp;
        }

        return $noHp;
    }

    public function welcome()
    {
        if (Auth::guard('member')->check()) {
            return redirect()->route('member.beranda');
        }

        return view('member.welcome');
    }

    public function tampilkanRegister()
    {
        return view('member.auth.register');
    }

    public function kirimOtp(Request $request)
    {
        $request->validate([
            'no_hp' => ['required', 'string', 'max:20'],
        ], [
            'no_hp.required' => 'Nomor HP wajib diisi.',
        ]);

        $noHp = $this->normalizeNoHp($request->no_hp);

        // Check if phone number already exists
        if (Member::where('no_hp', $noHp)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP ini sudah terdaftar. Silakan masuk.'
            ], 422);
        }

        // Generate 6 digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Hapus OTP lama yang belum dipakai untuk nomor ini
        OtpVerification::where('no_hp', $noHp)
            ->where('is_used', false)
            ->delete();

        // Simpan OTP baru ke database
        OtpVerification::create([
            'no_hp' => $noHp,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim ke nomor ' . $noHp,
            'otp' => $otp, // Exposed for demo display in modal
            'no_hp' => $noHp
        ]);
    }

    /**
     * Verifikasi kode OTP saat registrasi (Step 2)
     */
    public function verifikasiOtp(Request $request)
    {
        $request->validate([
            'no_hp' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'otp.required' => 'Kode OTP wajib diisi.',
        ]);

        $noHp = $this->normalizeNoHp($request->no_hp);

        $otpVerification = OtpVerification::where('no_hp', $noHp)
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otpVerification) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah.',
            ], 422);
        }

        if ($otpVerification->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kadaluarsa. Silakan minta OTP baru.',
            ], 422);
        }

        // Tandai OTP sudah dipakai (dicek ulang saat submit form di Step 3)
        $otpVerification->update(['is_used' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP valid.',
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:20', 'unique:members,no_hp'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'otp' => ['required', 'string'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar. Coba masuk saja.',
            'password.required' => 'Buat kata sandi dulu ya.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'otp.required' => 'Kode OTP wajib diisi.',
        ]);

        $noHp = $this->normalizeNoHp($data['no_hp']);

        // Verify OTP terhadap database (harus sudah diverifikasi di Step 2)
        $otpVerification = OtpVerification::where('no_hp', $noHp)
            ->where('otp', $data['otp'])
            ->where('is_used', true)
            ->latest()
            ->first();

        if (!$otpVerification || $otpVerification->isExpired()) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kadaluarsa.'])->withInput();
        }

        $member = Member::create([
            'nama' => $data['nama'],
            'no_hp' => $noHp,
            'password' => Hash::make($data['password']),
            'poin' => 0,
        ]);

        // Bersihkan OTP yang sudah dipakai untuk nomor ini
        OtpVerification::where('no_hp', $noHp)->delete();

        Auth::guard('member')->login($member);
        $request->session()->regenerate();

        return redirect()->route('member.beranda')->with('success', 'Selamat datang, ' . $member->nama . '! Akun member Saisho kamu sudah aktif.');
    }

    public function tampilkanLogin()
    {
        return view('member.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'no_hp' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'no_hp.required' => 'Masukkan nomor HP kamu.',
            'password.required' => 'Masukkan kata sandi.',
        ]);

        $noHpInput = trim($credentials['no_hp']);

        // Try direct input match, or normalized +62 match, or converted 08 match
        $phoneVariants = [$noHpInput];
        if (str_starts_with($noHpInput, '0')) {
            $phoneVariants[] = '+62' . substr($noHpInput, 1);
        } elseif (str_starts_with($noHpInput, '+62')) {
            $phoneVariants[] = '0' . substr($noHpInput, 3);
        } elseif (!str_starts_with($noHpInput, '+')) {
            $phoneVariants[] = '+62' . $noHpInput;
            $phoneVariants[] = '0' . $noHpInput;
        }

        $authenticated = false;
        foreach ($phoneVariants as $phone) {
            if (Auth::guard('member')->attempt(['no_hp' => $phone, 'password' => $credentials['password']], $request->boolean('ingat_saya'))) {
                $authenticated = true;
                break;
            }
        }

        if (!$authenticated) {
            return back()
                ->withErrors(['no_hp' => 'Nomor HP atau kata sandi salah.'])
                ->onlyInput('no_hp');
        }

        $request->session()->regenerate();

        $intendedUrl = $request->session()->pull('url.intended');

        if ($intendedUrl && str_starts_with(parse_url($intendedUrl, PHP_URL_PATH) ?? '', '/member')) {
            return redirect()->to($intendedUrl)->with('success', 'Berhasil masuk. Selamat datang kembali, ' . Auth::guard('member')->user()->nama . '!');
        }

        return redirect()->route('member.beranda')->with('success', 'Berhasil masuk. Selamat datang kembali, ' . Auth::guard('member')->user()->nama . '!');
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.welcome');
    }
}