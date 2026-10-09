<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $g = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('member.login')
                ->withErrors(['identifier' => 'Login dengan Google gagal. Coba lagi.']);
        }

        // 1) cari by google_id, 2) kalau belum ada, tautkan ke akun dengan email sama
        $member = Member::where('google_id', $g->getId())->first()
            ?? Member::where('email', $g->getEmail())->first();

        if ($member) {
            if (! $member->google_id) {
                $member->update(['google_id' => $g->getId()]);
            }
        } else {
            $base = Str::slug(Str::before($g->getEmail(), '@'), '');
            $username = $base;
            $i = 1;
            while (Member::where('username', $username)->exists()) {
                $username = $base . $i++;
            }

            $member = Member::create([
                'nama'      => $g->getName() ?: $base,
                'username'  => $username,
                'email'     => $g->getEmail(),
                'google_id' => $g->getId(),
                'password'  => null, // belum punya password; bisa dibuat sendiri lewat menu Ubah Password
            ]);

            $member->update(['kode_member' => Member::generateKodeMember($member->id)]);
        }

        Auth::guard('member')->login($member, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('member.beranda'));
    }
}