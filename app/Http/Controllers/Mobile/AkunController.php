<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AkunController extends Controller
{
    public function index()
    {
        try {
            $member = Auth::guard('member')->user();

            return view('mobile.akun', compact('member'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function profil()
    {
        try {
            $member = Auth::guard('member')->user();

            return view('mobile.profil', compact('member'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function updateProfil(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();

            $data = $request->validate([
                'nama' => ['required', 'string', 'max:100'],
                'username' => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'regex:/^[a-zA-Z0-9_.]+$/',
                    Rule::unique('members', 'username')->ignore($member->id),
                ],
                'email' => ['nullable', 'email', 'max:150', Rule::unique('members', 'email')->ignore($member->id)],
                'foto' => ['nullable', 'image', 'max:8192'],
            ], [
                'username.min' => 'Username must be at least 3 characters.',
                'username.regex' => 'Username may only contain letters, numbers, dots, and underscores.',
                'username.unique' => 'This username is already taken. Please choose another.',
            ]);

            if (! $member->kode_member) {
                $data['kode_member'] = \App\Models\Member::generateKodeMember($member->id);
            }

            $kodeMember = $member->kode_member ?: $data['kode_member'];

            if ($request->hasFile('foto')) {
                if ($member->kode_member) {
                    Storage::disk('public')->deleteDirectory('member/' . $kodeMember);
                }
                $data['foto'] = $request->file('foto')->store('member/' . $kodeMember, 'public');
            }

            $member->update($data);

            return redirect()->route('member.akun')->with('success', 'Your profile has been updated successfully.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapusAkun(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();

            if (! $member) {
                return redirect()->route('member.welcome');
            }

            // Jika akun bukan akun Google dan punya password, wajib verifikasi password
            $isGoogleAccount = !empty($member->google_id);

            if (! $isGoogleAccount && !is_null($member->password)) {
                $request->validate([
                    'password' => ['required', 'string'],
                ], [
                    'password.required' => 'Please enter your password to confirm.',
                ]);

                // Verifikasi password member:
                // 1. Password akun yang ter-hash
                // 2. Default password (nomor HP lokal 0xxxx atau +62xxxx) bila akun dari POS/admin
                // 3. Fallback 'password'
                $inputPassword = (string) $request->password;
                $passwordBenar = Hash::check($inputPassword, $member->password)
                    || ($inputPassword === Member::toLocalNoHp($member->no_hp))
                    || ($inputPassword === $member->no_hp)
                    || ($inputPassword === 'password');

                if (! $passwordBenar) {
                    return back()->withErrors(['password' => 'Incorrect password entered.'])->withInput();
                }
            }

            $memberId = $member->id;
            $kodeMember = $member->kode_member ?: Member::generateKodeMember($memberId);

            if ($kodeMember) {
                Storage::disk('public')->deleteDirectory('member/' . $kodeMember);
            } elseif ($member->foto && Storage::disk('public')->exists($member->foto)) {
                Storage::disk('public')->delete($member->foto);
            }

            // PENTING: Logout & invalidate session SEBELUM $member->delete().
            // Jika logout dipanggil setelah delete(), Laravel SessionGuard::logout() akan
            // memanggil $user->save() untuk me-refresh remember_token, yang menyebabkan
            // model yang sudah dihapus otomatis di-INSERT kembali ke DB!
            Auth::guard('member')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Hapus sesi database untuk member ini jika driver session adalah database
            if (config('session.driver') === 'database') {
                try {
                    \Illuminate\Support\Facades\DB::table('sessions')
                        ->where('user_id', $memberId)
                        ->delete();
                } catch (\Throwable $ignored) {}
            }

            // Hapus member secara permanen dari database
            $member->delete();

            return redirect()->route('member.welcome')->with('success', 'Your account has been deleted.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}