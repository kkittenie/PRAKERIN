<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanMember;

class PengaturanMemberController extends Controller
{
    public function edit()
    {
        $pengaturan = PengaturanMember::first();

        if (!$pengaturan) {
            $pengaturan = PengaturanMember::create([
                'aktif' => true,
                'minimal_belanja' => 10000,
                'poin' => 10,
            ]);
        }

        return view('pages.pengaturan-member.edit', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'aktif' => 'required|boolean',
            'minimal_belanja' => 'required|integer|min:1',
            'poin' => 'required|integer|min:1',
        ]); 

        $pengaturan = PengaturanMember::first();

        $pengaturan->update([
            'aktif' => $request->input('aktif'),
            'minimal_belanja' => $request->input('minimal_belanja'),
            'poin' => $request->input('poin'),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($pengaturan)
            ->withProperties([
                'aktif' => $request->input('aktif'),
                'minimal_belanja' => $request->input('minimal_belanja'),
                'poin' => $request->input('poin'),
            ])
            ->log('Pengaturan Member diperbarui');

        return back()->with('success', 'Pengaturan Member berhasil diperbarui.');
    }
}
