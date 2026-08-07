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
    public function tampilkanRegister()
    {
        return view('member.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:20', 'unique:members,no_hp'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar. Coba masuk saja.',
            'password.required' => 'Buat kata sandi dulu ya.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $member = Member::create([
            'nama' => $data['nama'],
            'no_hp' => $data['no_hp'],
            'password' => Hash::make($data['password']),
            'poin' => 0,
        ]);

        Auth::guard('member')->login($member);
        $request->session()->regenerate();

        return redirect()->route('member.beranda')->with('success', 'Selamat datang, '.$member->nama.'! Akun member kamu sudah aktif.');
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

        if (! Auth::guard('member')->attempt($credentials, $request->boolean('ingat_saya'))) {
            return back()
                ->withErrors(['no_hp' => 'Nomor HP atau kata sandi salah.'])
                ->onlyInput('no_hp');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('member.beranda'));
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login');
    }
}
