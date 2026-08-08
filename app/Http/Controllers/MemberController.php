<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $members = Member::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_hp', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.member.index', compact('members', 'search'));
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
            'email' => 'nullable|email|max:255',
            'tanggal_lahir' => 'nullable|date|before_or_equal:today',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
            'no_hp.unique' => 'Nomor HP sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $member = Member::create([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'email' => $request->email ?: null,
            'tanggal_lahir' => $request->tanggal_lahir ?: null,
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
            'email' => 'nullable|email|max:255',
            'tanggal_lahir' => 'nullable|date|before_or_equal:today',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
            'no_hp.unique' => 'Nomor HP sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $member->update([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'email' => $request->email ?: null,
            'tanggal_lahir' => $request->tanggal_lahir ?: null,
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
