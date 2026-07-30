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
    public function index(Request $request)
    {
            $search = $request->search;
            $periode = $request->periode ?? 'harian';
            $tanggalRefInput = $request->tanggal_ref;

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

    public function create()
    {
            $produk = Produk::orderBy('nama_produk')->get();

            $bahanBaku = BahanBaku::orderBy('nama_bahan')->get();

            return view(
                'pages.produksi.create',
                compact('produk','bahanBaku')
            );
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_produksi' => 'required|date_format:H:i',
            'produk_id' => 'required|exists:produks,id',
            'jumlah_hasil' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',

            'kadaluarsa_tipe' => 'required|in:tanggal,jam',
            'kadaluarsa_tanggal' => 'required_if:kadaluarsa_tipe,tanggal|nullable|date|after_or_equal:tanggal',
            'kadaluarsa_jam' => 'required_if:kadaluarsa_tipe,jam|nullable|integer|min:1',

            'bahan_baku_id' => 'required|array|min:1',

            'bahan_baku_id.*' => [
                'required',
                'distinct',
                'exists:bahan_bakus,id',
            ],
            
            'jumlah_terpakai' => 'required|array|min:1',

            'jumlah_terpakai.*' => [
                'required',
                'numeric',
                'gt:0',
            ],
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
        $produksi = null;
        DB::transaction(function () use ($request, &$produksi) {

        $produksi = Produksi::create([
            'tanggal' => $request->tanggal,
            'jam_produksi' => $request->jam_produksi,
            'produk_id' => $request->produk_id,
            'jumlah_hasil' => $request->jumlah_hasil,
            'user_id' => Auth::id(),
            'keterangan' => $request->keterangan,
            'kadaluarsa_tipe' => $request->kadaluarsa_tipe,
            'kadaluarsa_tanggal' => $request->kadaluarsa_tipe === 'tanggal' ? $request->kadaluarsa_tanggal : null,
            'kadaluarsa_jam' => $request->kadaluarsa_tipe === 'jam' ? $request->kadaluarsa_jam : null,
        ]);

            foreach ($request->bahan_baku_id as $i => $id) {

                $pakai = (float) $request->jumlah_terpakai[$i];

                if ($pakai <= 0) {
                    continue;
                }

                $bahan = BahanBaku::lockForUpdate()->findOrFail($id);

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

        activity()
            ->causedBy(auth()->user())
            ->performedOn($produksi)
            ->withProperties([
                'produk_id' => $request->produk_id,
                'jumlah_hasil' => $request->jumlah_hasil,
                'bahan_baku_id' => $request->bahan_baku_id,
                'jumlah_terpakai' => $request->jumlah_terpakai,
            ])
            ->log('menambahkan produksi');

        return redirect()
            ->route('produksi.index')
            ->with('success','Produksi berhasil disimpan.');
    } catch (\Exception $e) {

        return back()
            ->withInput()
            ->with('error', $e->getMessage());

    }
    }

    public function show(Produksi $produksi)
    {
        $produksi->load([
            'produk',
            'user',
            'detailBahan.bahanBaku.satuan'
        ]);

        return view(
            'pages.produksi.show',
            compact('produksi')
        );
    }

    public function edit(Produksi $produksi)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        return back()->with(
        'error',
        'Data produksi tidak dapat dihapus.'
    );
    }
}
