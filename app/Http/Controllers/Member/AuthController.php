<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
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

        $noHp = trim($request->no_hp);

        // Normalize phone number
        if (str_starts_with($noHp, '0')) {
            $noHp = '+62' . substr($noHp, 1);
        } elseif (!str_starts_with($noHp, '+')) {
            $noHp = '+62' . $noHp;
        }

        // Check if phone number already exists
        if (Member::where('no_hp', $noHp)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP ini sudah terdaftar. Silakan masuk.'
            ], 422);
        }

        // Generate 6 digit OTP
        $otp = (string) rand(100000, 999999);

        session([
            'otp_code' => $otp,
            'otp_phone' => $noHp,
            'otp_expires_at' => now()->addMinutes(5)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim ke nomor ' . $noHp,
            'otp' => $otp, // Exposed for demo display in modal
            'no_hp' => $noHp
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

        // Verify OTP (allow session otp or fallback demo otp '123456')
        $sessionOtp = session('otp_code');
        $sessionPhone = session('otp_phone');

        if ($data['otp'] !== '123456' && $data['otp'] !== $sessionOtp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kadaluarsa.'])->withInput();
        }

        $noHp = trim($data['no_hp']);
        if (str_starts_with($noHp, '0')) {
            $noHp = '+62' . substr($noHp, 1);
        } elseif (!str_starts_with($noHp, '+')) {
            $noHp = '+62' . $noHp;
        }

        $member = Member::create([
            'nama' => $data['nama'],
            'no_hp' => $noHp,
            'password' => Hash::make($data['password']),
            'poin' => 0,
        ]);

        // Clear OTP session
        session()->forget(['otp_code', 'otp_phone', 'otp_expires_at']);

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
            return redirect()->to($intendedUrl);
        }

        return redirect()->route('member.beranda');
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.welcome');
    }
}
