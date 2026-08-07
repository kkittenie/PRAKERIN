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
            'email' => ['nullable', 'email', 'max:150', Rule::unique('members', 'email')->ignore($member->id)],
            'tanggal_lahir' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'foto' => ['nullable', 'image', 'max:2048'],
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
}