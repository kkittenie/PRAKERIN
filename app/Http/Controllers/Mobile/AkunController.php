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

            // Member lama yang daftar lewat mobile sebelum bug generate
            // kode_member pas register diperbaiki mungkin masih punya
            // kode_member NULL -- isi & simpan sekarang juga (bukan cuma
            // dipakai buat nama folder foto doang) supaya member ini juga
            // langsung bisa dicari di POS pakai kode membernya.
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

            return redirect()->route('member.akun')->with('success', 'Profil kamu berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapusAkun(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();
            $kodeMember = $member->kode_member ?: \App\Models\Member::generateKodeMember($member->id);

            if ($kodeMember) {
                Storage::disk('public')->deleteDirectory('member/' . $kodeMember);
            } elseif ($member->foto) {
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