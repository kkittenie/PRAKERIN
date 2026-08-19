<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');

            $members = Member::query()
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->get();

            return view('pages.member.index', compact('members', 'search'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('pages.member.create', ['title' => 'Tambah Member']);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Normalisasi no_hp ke format +62xxxx SEBELUM divalidasi & disimpan,
            // supaya sama persis dengan format yang dipakai member app saat login.
            // Kalau ini tidak dilakukan, no_hp bisa tersimpan mentah sesuai ketikan
            // kasir (mis. "081234..." dengan spasi/strip/format beda-beda), sehingga
            // password default (= no_hp) bisa tidak cocok saat member app membangun
            // ulang no_hp dalam format +62 miliknya sendiri.
            $request->merge(['no_hp' => Member::normalizeNoHp($request->no_hp)]);

            $request->validate([
                'nama' => 'required|string|max:255',
                'username' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_.]+$/|unique:members,username',
                'no_hp' => 'required|string|max:20|unique:members,no_hp',
                'email' => 'nullable|email|max:255',
                'tanggal_lahir' => 'nullable|date|before_or_equal:today',
            ], [
                'nama.required' => 'Nama wajib diisi.',
                'nama.string' => 'Nama harus berupa teks.',
                'nama.max' => 'Nama maksimal 255 karakter.',
                'username.required' => 'Username wajib diisi.',
                'username.min' => 'Username minimal 3 karakter.',
                'username.regex' => 'Username hanya boleh huruf, angka, titik, dan underscore (tanpa spasi).',
                'username.unique' => 'Username sudah digunakan.',
                'no_hp.required' => 'Nomor HP wajib diisi.',
                'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
                'no_hp.unique' => 'Nomor HP sudah digunakan.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 255 karakter.',
                'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
                'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            ]);

            // Password default pakai format LOKAL (0xxxx), bukan +62xxxx, biar
            // gampang diinget & diketik member (no_hp di DB tetap disimpan +62xxxx).
            $passwordDefault = Member::toLocalNoHp($request->no_hp);

            $member = Member::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'no_hp' => $request->no_hp,
                'email' => $request->email ?: null,
                'tanggal_lahir' => $request->tanggal_lahir ?: null,
                'poin' => 0,
                // Default password saat member dibuat dari POS = nomor HP-nya sendiri
                // (format lokal 0xxxx), supaya member bisa langsung login.
                // Member disarankan mengganti password ini lewat menu "Change Password".
                'password' => Hash::make($passwordDefault),
            ]);

            activity()->causedBy(auth()->user())->performedOn($member)->log("Menambah member: {$member->nama}");

            return redirect()->route('member.index')->with('success', "Member berhasil ditambahkan. Password default: {$passwordDefault} (sama dengan nomor HP).");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
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
        try {
            return view('pages.member.edit', ['title' => 'Edit Member', 'member' => $member]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        try {
            // Normalisasi juga saat edit, supaya no_hp tetap konsisten kalau admin
            // mengubahnya (mis. dari "081234..." jadi format lain).
            $request->merge(['no_hp' => Member::normalizeNoHp($request->no_hp)]);

            $request->validate([
                'nama' => 'required|string|max:255',
                'username' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_.]+$/|unique:members,username,' . $member->id,
                'no_hp' => 'required|string|max:20|unique:members,no_hp,' . $member->id,
                'email' => 'nullable|email|max:255',
                'tanggal_lahir' => 'nullable|date|before_or_equal:today',
            ], [
                'nama.required' => 'Nama wajib diisi.',
                'nama.string' => 'Nama harus berupa teks.',
                'nama.max' => 'Nama maksimal 255 karakter.',
                'username.required' => 'Username wajib diisi.',
                'username.min' => 'Username minimal 3 karakter.',
                'username.regex' => 'Username hanya boleh huruf, angka, titik, dan underscore (tanpa spasi).',
                'username.unique' => 'Username sudah digunakan.',
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
                'username' => $request->username,
                'no_hp' => $request->no_hp,
                'email' => $request->email ?: null,
                'tanggal_lahir' => $request->tanggal_lahir ?: null,
            ]);

            activity()->causedBy(auth()->user())->performedOn($member)->log("Mengubah member: {$member->nama}");

            return redirect()->route('member.index')->with('success', 'Member berhasil diupdate');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Reset password member kembali ke default (= no. HP format lokal 0xxxx).
     * Dipakai dari tombol "Reset Password" di halaman daftar member, untuk
     * member yang lupa password atau akunnya dibuat sebelum fix format no_hp.
     */
    public function resetPassword(Member $member)
    {
        try {
            $passwordBaru = Member::toLocalNoHp($member->no_hp);

            $member->update([
                'password' => Hash::make($passwordBaru),
            ]);

            activity()->causedBy(auth()->user())->performedOn($member)->log("Mereset password member: {$member->nama}");

            return back()->with('success', "Password {$member->nama} berhasil direset ke: {$passwordBaru} (sama dengan nomor HP).");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        try {
            $member->delete();
            return back()->with('success', 'Member dihapus');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}