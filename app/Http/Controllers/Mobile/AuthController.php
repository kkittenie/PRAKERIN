<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberDeviceToken;
use App\Models\OtpVerification;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Normalisasi nomor HP ke format +62xxxx
     */
    private function normalizeNoHp(string $noHp): string
    {
        return Member::normalizeNoHp($noHp);
    }

    public function welcome()
    {
        try {
            if (Auth::guard('member')->check()) {
                return redirect()->route('member.beranda');
            }

            // Slide welcome dinamis dari admin; kosong = pakai tampilan bawaan di view.
            $slides = \App\Models\WelcomeSlide::tampil()->get();

            return view('mobile.welcome', compact('slides'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tampilkanRegister()
    {
        try {
            return view('mobile.auth.register');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function kirimOtp(Request $request)
    {
        try {
            $request->validate([
                'no_hp' => ['required', 'string', 'max:20'],
            ], [
                'no_hp.required' => 'Phone number is required.',
            ]);

            $noHp = $this->normalizeNoHp($request->no_hp);

            if (Member::where('no_hp', $noHp)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number is already registered. Please sign in.'
                ], 422);
            }

            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            OtpVerification::where('no_hp', $noHp)
                ->where('is_used', false)
                ->delete();

            OtpVerification::create([
                'no_hp' => $noHp,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(5),
                'is_used' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP code sent successfully to ' . $noHp,
                'otp' => $otp,
                'no_hp' => $noHp
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Verifikasi kode OTP saat registrasi (Step 2)
     */
    public function verifikasiOtp(Request $request)
    {
        try {
            $request->validate([
                'no_hp' => ['required', 'string', 'max:20'],
                'otp' => ['required', 'string', 'size:6'],
            ], [
                'no_hp.required' => 'Phone number is required.',
                'otp.required' => 'OTP code is required.',
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
                    'message' => 'Invalid OTP code.',
                ], 422);
            }

            if ($otpVerification->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP code has expired. Please request a new OTP.',
                ], 422);
            }

            $otpVerification->update(['is_used' => true]);

            return response()->json([
                'success' => true,
                'message' => 'OTP code is valid.',
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'nama' => ['required', 'string', 'max:255'],
                'username' => [
                    'required',
                    'string',
                    'max:50',
                    'min:3',
                    'regex:/^[a-zA-Z0-9_.]+$/',
                    'unique:members,username',
                ],
                'no_hp' => ['required', 'string', 'max:20', 'unique:members,no_hp'],
                'password' => ['required', 'confirmed', Password::min(6)],
                'otp' => ['required', 'string'],
            ], [
                'nama.required' => 'Name is required.',
                'username.required' => 'Username is required.',
                'username.min' => 'Username must be at least 3 characters.',
                'username.regex' => 'Username may only contain letters, numbers, dots, and underscores.',
                'username.unique' => 'This username is already taken. Please choose another.',
                'no_hp.required' => 'Phone number is required.',
                'no_hp.unique' => 'This phone number is already registered. Please sign in.',
                'password.required' => 'Please create a password.',
                'password.confirmed' => 'Password confirmation does not match.',
                'otp.required' => 'OTP code is required.',
            ]);

            $noHp = $this->normalizeNoHp($data['no_hp']);

            $otpVerification = OtpVerification::where('no_hp', $noHp)
                ->where('otp', $data['otp'])
                ->where('is_used', true)
                ->latest()
                ->first();

            if (!$otpVerification || $otpVerification->isExpired()) {
                return back()->withErrors(['otp' => 'OTP code is invalid or has expired.'])->withInput();
            }

            $member = Member::create([
                'nama' => $data['nama'],
                'username' => $data['username'],
                'no_hp' => $noHp,
                'password' => Hash::make($data['password']),
                'poin' => 0,
            ]);

            $member->kode_member = Member::generateKodeMember($member->id);
            $member->save();

            OtpVerification::where('no_hp', $noHp)->delete();

            Auth::guard('member')->login($member);
            $request->session()->regenerate();

            return redirect()->route('member.beranda')->with('success', 'Welcome, ' . $member->nama . '!');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tampilkanLogin()
    {
        try {
            if (Auth::guard('web')->check()) {
                return redirect()->route('dashboard');
            }

            if (Auth::guard('member')->check()) {
                return redirect()->route('member.beranda');
            }

            return view('mobile.auth.login');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Login pelanggan (member). Satu kolom "identifier":
     * kalau isinya kelihatan kayak nomor HP -> dicocokin ke no_hp,
     * selain itu (atau kalau no HP-nya gak ketemu) -> dicocokin ke username.
     * Akun admin TIDAK bisa login lewat sini, adanya di /admin/login.
     */
    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'identifier' => ['required', 'string'],
                'password' => ['required', 'string'],
            ], [
                'identifier.required' => 'Username or phone number is required.',
                'password.required' => 'Please enter your password.',
            ]);

            $identifier = trim($data['identifier']);
            $throttleKey = 'member|' . Str::lower($identifier) . '|' . $request->ip();

            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                $seconds = RateLimiter::availableIn($throttleKey);

                return back()
                    ->withErrors(['identifier' => "Too many failed attempts. Please try again in {$seconds} seconds."])
                    ->onlyInput('identifier');
            }

            $remember = $request->boolean('ingat_saya');
            $guard = Auth::guard('member');

            // Kelihatan kayak nomor HP (angka, boleh diawali +, spasi, atau strip)
            if (preg_match('/^\+?[\d\s\-]+$/', $identifier)) {
                $noHp = $this->normalizeNoHp($identifier);
                $phoneVariants = array_unique([$noHp, Member::toLocalNoHp($noHp)]);

                foreach ($phoneVariants as $phone) {
                    if ($guard->attempt(['no_hp' => $phone, 'password' => $data['password']], $remember)) {
                        RateLimiter::clear($throttleKey);
                        return $this->redirectSetelahLoginMember($request);
                    }
                }
            }

            // Username (juga jadi cadangan kalau ada username yang isinya angka semua)
            if ($guard->attempt(['username' => $identifier, 'password' => $data['password']], $remember)) {
                RateLimiter::clear($throttleKey);
                return $this->redirectSetelahLoginMember($request);
            }

            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors(['identifier' => 'Incorrect username/phone number or password.'])
                ->onlyInput('identifier');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function redirectSetelahLoginMember(Request $request)
    {
        $request->session()->regenerate();

        $intendedUrl = $request->session()->pull('url.intended');
        $sapaan = 'Signed in successfully. Welcome back, ' . Auth::guard('member')->user()->nama . '!';

        if ($intendedUrl && str_starts_with(parse_url($intendedUrl, PHP_URL_PATH) ?? '', '/mobile')) {
            return redirect()->to($intendedUrl)->with('success', $sapaan);
        }

        return redirect()->route('member.beranda')->with('success', $sapaan);
    }

    public function logout(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();

            $fcmToken = $request->input('fcm_token')
                ?: $request->cookie('fcm_token')
                ?: ($request->session()->has('fcm_token') ? $request->session()->get('fcm_token') : null);

            if (!empty($fcmToken)) {
                MemberDeviceToken::where('token', $fcmToken)->delete();
            }

            if ($member) {
                MemberDeviceToken::where('member_id', $member->id)->delete();
            }

            Auth::guard('member')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('member.welcome')
                ->withoutCookie('fcm_token')
                ->withoutCookie('fcm_device_token');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}