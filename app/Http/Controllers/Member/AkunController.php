<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AkunController extends Controller
{
    public function index()
    {
        $member = Auth::guard('member')->user();

        return view('member.akun', compact('member'));
    }

    public function profil()
    {
        $member = Auth::guard('member')->user();

        return view('member.profil', compact('member'));
    }

    public function updateProfil(Request $request)
    {
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
            $data['foto'] = $request->file('foto')->store('member-foto', 'public');
        }

        $member->update($data);

        return redirect()->route('member.akun')->with('success', 'Profil kamu berhasil diperbarui.');
    }

    public function hapusAkun(Request $request)
    {
        $member = Auth::guard('member')->user();

        if ($member->foto) {
            Storage::disk('public')->delete($member->foto);
        }

        $member->delete();

        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.welcome')->with('success', 'Akun kamu berhasil dihapus.');
    }
}