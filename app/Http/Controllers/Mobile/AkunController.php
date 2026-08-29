<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'username.min' => 'Username minimal 3 karakter.',
                'username.regex' => 'Username hanya boleh huruf, angka, titik, dan underscore (tanpa spasi).',
                'username.unique' => 'Username ini sudah dipakai. Coba username lain.',
            ]);

            if ($request->hasFile('foto')) {
                if ($member->foto) {
                    Storage::disk('public')->delete($member->foto);
                }
                $kodeMember = $member->kode_member ?: \App\Models\Member::generateKodeMember($member->id);
                $data['foto'] = $request->file('foto')->store('member/' . $kodeMember, 'public');
            }

            $member->update($data);

            return redirect()->route('member.akun')->with('success', 'Profil kamu berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapusAkun(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();

            if ($member->foto) {
                Storage::disk('public')->delete($member->foto);
            }

            $member->delete();

            Auth::guard('member')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('member.welcome')->with('success', 'Akun kamu berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}