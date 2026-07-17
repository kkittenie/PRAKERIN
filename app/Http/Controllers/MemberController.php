<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = Member::orderBy('nama')->get();
        return view('pages.member.index', ['title' => 'Kelola Member', 'members' => $members]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.member.create', ['title' => 'Tambah Member']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20|unique:members,no_hp',
        ]);

        $member = Member::create([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'poin' => 0,
        ]);

        activity()->causedBy(auth()->user())->performedOn($member)->log("Menambah member: {$member->nama}");

        return redirect()->route('member.index')->with('success', 'Member berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        return view('pages.member.edit', ['title' => 'Edit Member', 'member' => $member]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20|unique:members,no_hp,' . $member->id,
        ]);

        $member->update([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
        ]);

        activity()->causedBy(auth()->user())->performedOn($member)->log("Mengubah member: {$member->nama}");

        return redirect()->route('member.index')->with('success', 'Member berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        $member->delete();
        return back()->with('success', 'Member dihapus');
    }
}
