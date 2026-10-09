<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminLoginController extends Controller
{
    /**
     * Halaman login admin (/admin/login).
     * Pakai view yang sama dengan login pelanggan, bedanya cuma $mode.
     */
    public function create()
    {
        try {
            if (Auth::guard('web')->check()) {
                return redirect()->route('dashboard');
            }

            return view('mobile.auth.login', ['mode' => 'admin']);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Proses login admin. Hanya guard "web" (tabel users), username atau email.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'identifier' => ['required', 'string'],
                'password' => ['required', 'string'],
            ], [
                'identifier.required' => 'Username atau email wajib diisi.',
                'password.required' => 'Masukkan kata sandi.',
            ]);

            $identifier = trim($data['identifier']);
            $throttleKey = 'admin|' . Str::lower($identifier) . '|' . $request->ip();

            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                $seconds = RateLimiter::availableIn($throttleKey);

                return back()
                    ->withErrors(['identifier' => "Terlalu banyak percobaan gagal. Coba lagi dalam {$seconds} detik."])
                    ->onlyInput('identifier');
            }

            $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            if (Auth::guard('web')->attempt([$field => $identifier, 'password' => $data['password']], $request->boolean('ingat_saya'))) {
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();

                $user = Auth::guard('web')->user();
                activity()
                    ->causedBy($user)
                    ->performedOn($user)
                    ->withProperties([
                        'user' => $user->name,
                        'email' => $user->email,
                    ])
                    ->log('Login ke sistem');

                return redirect()->intended(route('dashboard'));
            }

            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors(['identifier' => 'Username/email atau kata sandi salah.'])
                ->onlyInput('identifier');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}