<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
            $search = $request->search;

            $bahanBaku = BahanBaku::when($search, function ($query) use ($search) {
                $query->where('nama_bahan', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

            return view(
                'pages.bahan-baku.index',
                compact('bahanBaku', 'search')
            );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.bahan-baku.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:100|unique:bahan_bakus,nama_bahan',
            'satuan' => 'required|string|max:20',
            'stok' => 'required|numeric|min:0',
            'stok_minimum' => 'required|numeric|min:0',
        ]);

        BahanBaku::create($validated);

        return redirect()
            ->route('bahan-baku.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
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
    public function edit(BahanBaku $bahan_baku)
    {
        return view('pages.bahan-baku.edit', compact('bahan_baku'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BahanBaku $bahan_baku)
    {
        $validated = $request->validate([
            'nama_bahan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('bahan_bakus', 'nama_bahan')
                    ->ignore($bahan_baku->id),
            ],
            'satuan' => 'required|string|max:20',
            'stok' => 'required|numeric|min:0',
            'stok_minimum' => 'required|numeric|min:0',
        ]);

        $bahan_baku->update($validated);

        return redirect()
            ->route('bahan-baku.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BahanBaku $bahan_baku)
    {
         $bahan_baku->delete();

        return redirect()
            ->route('bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
