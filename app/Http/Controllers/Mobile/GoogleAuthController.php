<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Alur login/daftar dengan Google (mirip GitHub):
 *  - Akun sudah ada      -> langsung login.
 *  - Akun belum ada      -> diarahkan ke halaman daftar, data Google sudah terisi.
 *                           Langkah 1: nama & username. Langkah 2: kata sandi + konfirmasi.
 *                           Akun baru baru dibuat setelah pengguna menekan "Buat akun".
 */
class GoogleAuthController extends Controller
{
    private const SESSION_KEY = 'google_signup';

    public function redirect(Request $request)
    {
        // Mulai ulang alur: buang data pendaftaran Google yang tertinggal
        $request->session()->forget(self::SESSION_KEY);

        $driver = Socialite::driver('google');

        // "Gunakan akun Google lain" -> paksa Google menampilkan pemilih akun
        if ($request->boolean('pilih_akun')) {
            $driver->with(['prompt' => 'select_account']);
        }

        return $driver->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $g = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('member.login')
                ->withErrors(['identifier' => 'Login dengan Google gagal. Coba lagi.']);
        }

        // 1) cari by google_id
        $member = Member::where('google_id', $g->getId())->first();

        // 2) belum tertaut: tautkan ke akun dengan email yang sama (hanya jika email terverifikasi Google)
        if (! $member && $g->getEmail() && ($g->user['email_verified'] ?? true)) {
            $member = Member::where('email', $g->getEmail())->first();

            if ($member) {
                $member->update(['google_id' => $g->getId()]);
            }
        }

        if ($member) {
            return $this->loginMember($request, $member);
        }

        // 3) belum punya akun -> ke halaman daftar dengan data Google terisi
        $request->session()->put(self::SESSION_KEY, [
            'google_id' => $g->getId(),
            'nama'      => $g->getName() ?: Str::before((string) $g->getEmail(), '@'),
            'email'     => $g->getEmail(),
        ]);

        return redirect()->route('member.google.register');
    }

    public function tampilkanRegister(Request $request)
    {
        $g = $request->session()->get(self::SESSION_KEY);

        if (! $g) {
            return redirect()->route('member.login');
        }

        return view('mobile.auth.google-register', [
            'google'            => $g,
            'usernameSaran'     => $this->sarankanUsername($g['email'] ?? '', $g['nama']),
        ]);
    }

    public function register(Request $request)
    {
        $g = $request->session()->get(self::SESSION_KEY);

        if (! $g) {
            return redirect()->route('member.login')
                ->withErrors(['identifier' => 'Sesi pendaftaran Google berakhir. Silakan coba lagi.']);
        }

        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_.]+$/', 'unique:members,username'],
            'no_hp'    => ['required', 'string', 'max:20'],
        ], [
            'nama.required'     => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.min'      => 'Username minimal 3 karakter.',
            'username.regex'    => 'Username hanya boleh huruf, angka, titik, dan underscore (tanpa spasi).',
            'username.unique'   => 'Username ini sudah dipakai. Coba username lain.',
            'no_hp.required'    => 'Nomor HP wajib diisi.',
        ]);

        $noHp = Member::normalizeNoHp($data['no_hp']);

        if (Member::where('no_hp', $noHp)->exists()) {
            return back()->withErrors(['no_hp' => 'Nomor HP ini sudah terdaftar. Gunakan nomor lain atau langsung masuk.'])->withInput();
        }

        // Nomor HP format lokal (diawali angka 0, contoh: 088xxxx) dijadikan password awal member
        $localNoHp = Member::toLocalNoHp($noHp);

        // Jaga-jaga: akun dengan google_id/email ini sudah dibuat sejak callback (mis. tab ganda)
        $member = Member::where('google_id', $g['google_id'])->first();

        if (! $member) {
            $member = Member::create([
                'nama'      => $data['nama'],
                'username'  => $data['username'],
                'email'     => $g['email'],
                'no_hp'     => $noHp,
                'google_id' => $g['google_id'],
                'poin'      => 0,
                'password'  => $localNoHp, // di-hash otomatis oleh cast 'hashed' di model Member
            ]);

            $member->update(['kode_member' => Member::generateKodeMember($member->id)]);
        } else {
            $member->update([
                'nama'      => $data['nama'],
                'username'  => $data['username'],
                'no_hp'     => $noHp,
                'password'  => $localNoHp,
            ]);
        }

        $request->session()->forget(self::SESSION_KEY);

        return $this->loginMember($request, $member, 'Selamat datang, ' . $member->nama . '! Akun kamu berhasil didaftarkan.');
    }

    /** Batalkan pendaftaran Google (tombol "Batal"). */
    public function batal(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('member.login');
    }

    /** Cek ketersediaan username secara langsung (centang hijau seperti GitHub). */
    public function cekUsername(Request $request)
    {
        $username = (string) $request->query('username', '');

        if (strlen($username) < 3 || strlen($username) > 50 || ! preg_match('/^[a-zA-Z0-9_.]+$/', $username)) {
            return response()->json([
                'available' => false,
                'message'   => 'Minimal 3 karakter; hanya huruf, angka, titik, dan underscore.',
            ]);
        }

        $tersedia = ! Member::where('username', $username)->exists();

        return response()->json([
            'available' => $tersedia,
            'message'   => $tersedia ? 'Username tersedia.' : 'Username ini sudah dipakai.',
        ]);
    }

    private function loginMember(Request $request, Member $member, ?string $pesan = null)
    {
        Auth::guard('member')->login($member, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('member.beranda'))
            ->with('success', $pesan ?? 'Berhasil masuk. Selamat datang kembali, ' . $member->nama . '!');
    }

    private function sarankanUsername(string $email, string $nama): string
    {
        $base = Str::slug(Str::before($email, '@') ?: $nama, '');
        $base = $base !== '' ? $base : 'member';
        $username = $base;
        $i = 1;

        while (Member::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        return $username;
    }
}