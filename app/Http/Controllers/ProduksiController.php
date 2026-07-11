<?php

namespace App\Http\Controllers;

use App\Models\Produksi;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\ProduksiBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ProduksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
            $search = $request->search;

            $produksi = Produksi::with(['produk','user'])
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('produk', function ($q) use ($search) {
                        $q->where('nama_produk', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10);

            return view(
                'pages.produksi.index',
                compact('produksi','search')
            );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
            $produk = Produk::orderBy('nama_produk')->get();

            $bahanBaku = BahanBaku::orderBy('nama_bahan')->get();

            return view(
                'pages.produksi.create',
                compact('produk','bahanBaku')
            );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'produk_id' => 'required|exists:produks,id',
            'jumlah_hasil' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',

            'bahan_baku_id' => 'required|array|min:1',
            'jumlah_terpakai.*' => 'required|numeric|min:0',
        ]);

        $adaBahan = false;

        foreach ($request->jumlah_terpakai as $jumlah) {

            if ($jumlah > 0) {
                $adaBahan = true;
                break;
            }

        }

        if (!$adaBahan) {

            return back()
                ->withInput()
                ->with('error', 'Minimal pilih satu bahan baku yang digunakan.');

        }

    try {
        DB::transaction(function () use ($request) {

            $produksi = Produksi::create([
                'tanggal' => $request->tanggal,
                'produk_id' => $request->produk_id,
                'jumlah_hasil' => $request->jumlah_hasil,
                'user_id' => Auth::id(),
                'keterangan' => $request->keterangan,
            ]);

            foreach ($request->bahan_baku_id as $i => $id) {

                $pakai = (float) $request->jumlah_terpakai[$i];

                if ($pakai <= 0) {
                    continue;
                }

                $bahan = BahanBaku::findOrFail($id);

                if ($bahan->stok < $pakai) {
                    throw new Exception(
                        "Stok {$bahan->nama_bahan} tidak mencukupi."
                    );
                }

                ProduksiBahanBaku::create([
                    'produksi_id' => $produksi->id,
                    'bahan_baku_id' => $id,
                    'jumlah_terpakai' => $pakai,
                ]);

                $bahan->decrement('stok', $pakai);
            }

            $produk = Produk::findOrFail($request->produk_id);

            $produk->increment('stok', $request->jumlah_hasil);

        });

        return redirect()
            ->route('produksi.index')
            ->with('success','Produksi berhasil disimpan.');
    } catch (\Exception $e) {

        return back()
            ->withInput()
            ->with('error', $e->getMessage());

    }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produksi $produksi)
    {
        $produksi->load([
            'produk',
            'user',
            'detailBahan.bahanBaku'
        ]);

        return view(
            'pages.produksi.show',
            compact('produksi')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produksi $produksi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return back()->with(
        'error',
        'Data produksi tidak dapat dihapus.'
    );
    }
}
