<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        return $this->lanjutkanDenganAkunGoogle(
            $request,
            (string) $g->getId(),
            $g->getEmail(),
            $g->getName(),
            (bool) ($g->user['email_verified'] ?? true)
        );
    }

    /**
     * Login Google native dari aplikasi Android. Aplikasi mengirim ID token (Credential Manager),
     * di sini diverifikasi ke Google lalu alurnya sama persis dengan callback OAuth biasa.
     */
    public function loginDenganToken(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        $request->validate(['id_token' => ['required', 'string', 'max:4096']]);

        Log::info('[google-native] token diterima dari aplikasi', [
            'panjang_token' => strlen((string) $request->input('id_token')),
            'host'          => $request->getHost(),
        ]);

        $g = $this->verifikasiIdToken((string) $request->input('id_token'));

        if (! $g) {
            return redirect()->route('member.login')
                ->withErrors(['identifier' => 'Login dengan Google gagal. Coba lagi.']);
        }

        return $this->lanjutkanDenganAkunGoogle($request, $g['id'], $g['email'], $g['nama'], $g['email_terverifikasi']);
    }

    /** Alur bersama callback OAuth & login native: login / tautkan / arahkan ke pendaftaran. */
    private function lanjutkanDenganAkunGoogle(Request $request, string $googleId, ?string $email, ?string $nama, bool $emailTerverifikasi)
    {
        // 1) cari by google_id
        $member = Member::where('google_id', $googleId)->first();

        // 2) belum tertaut: tautkan ke akun dengan email yang sama (hanya jika email terverifikasi Google)
        if (! $member && $email && $emailTerverifikasi) {
            $member = Member::where('email', $email)->first();

            if ($member) {
                $member->update(['google_id' => $googleId]);
            }
        }

        if ($member) {
            return $this->loginMember($request, $member);
        }

        // 3) belum punya akun -> ke halaman daftar dengan data Google terisi
        Log::info('[google-native] akun belum terdaftar, diarahkan ke form daftar', ['email' => $email]);

        $request->session()->put(self::SESSION_KEY, [
            'google_id' => $googleId,
            'nama'      => $nama ?: Str::before((string) $email, '@'),
            'email'     => $email,
        ]);

        return redirect()->route('member.google.register');
    }

    /** Verifikasi ID token ke Google (tokeninfo): tanda tangan, masa berlaku, penerbit, dan audience. */
    private function verifikasiIdToken(string $idToken): ?array
    {
        try {
            $res = Http::timeout(10)->acceptJson()
                ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);
        } catch (\Throwable $e) {
            Log::warning('[google-native] gagal menghubungi Google (tokeninfo): ' . $e->getMessage());

            return null;
        }

        if (! $res->ok()) {
            Log::warning('[google-native] tokeninfo ditolak Google', [
                'status' => $res->status(),
                'body'   => mb_substr($res->body(), 0, 300),
            ]);

            return null;
        }

        $d = $res->json();
        $clientId = (string) config('services.google.client_id');
        $issuerValid = in_array($d['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);

        if ($clientId === '' || ($d['aud'] ?? null) !== $clientId || ! $issuerValid
            || empty($d['sub']) || (int) ($d['exp'] ?? 0) < time()) {
            Log::warning('[google-native] token tidak lolos pemeriksaan', [
                'aud_token'       => $d['aud'] ?? null,
                'client_id_laravel' => $clientId,
                'iss_valid'       => $issuerValid,
                'exp'             => $d['exp'] ?? null,
                'sekarang'        => time(),
            ]);

            return null;
        }

        return [
            'id'                  => (string) $d['sub'],
            'email'               => $d['email'] ?? null,
            'nama'                => $d['name'] ?? null,
            'email_terverifikasi' => filter_var($d['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
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