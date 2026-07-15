<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $pegawai = Pegawai::when($search, function ($query) use ($search) {

            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");

        })
        ->latest()
        ->paginate(10);

        return view(
            'pages.pegawai.index',
            compact('pegawai', 'search')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.pegawai.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'no_hp' => 'required|regex:/^[0-9+\-\s()]+$/|max:20',
            'alamat' => 'required|string',
            'jabatan' => 'required|in:Admin,Kasir,Pegawai',
            'tanggal_masuk' => 'required|date',
            'status_aktif' => 'required|boolean',
        ]);

        Pegawai::create([
        'nama' => $request->nama,
        'no_hp' => $request->no_hp,
        'alamat' => $request->alamat,
        'jabatan' => $request->jabatan,
        'tanggal_masuk' => $request->tanggal_masuk,
        'status_aktif' => $request->status_aktif,
        'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pegawai $pegawai)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pegawai $pegawai)
    {
        return view(
            'pages.pegawai.edit',
            compact('pegawai')
        );
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'no_hp' => 'required|regex:/^[0-9+\-\s()]+$/|max:20',
            'alamat' => 'required|string',
            'jabatan' => 'required|in:Admin,Kasir,Pegawai',
            'tanggal_masuk' => 'required|date',
            'status_aktif' => 'required|boolean',
        ]);

        $pegawai->update([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'jabatan' => $request->jabatan,
            'tanggal_masuk' => $request->tanggal_masuk,
            'status_aktif' => $request->status_aktif,
        ]);

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }
}