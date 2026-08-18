<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Satuan;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->search;

            $bahanBaku = BahanBaku::with('satuan')
                ->when($search, function ($query) use ($search) {
                    $query->where('nama_bahan', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10);

            return view(
                'pages.bahan-baku.index',
                compact('bahanBaku', 'search')
            );
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            $satuan = Satuan::orderBy('nama')->get();

            return view(
                'pages.bahan-baku.create',
                compact('satuan')
            );
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $satuan = Satuan::find($request->satuan_id);

            $aturanBulat = function ($attribute, $value, $fail) use ($satuan) {
                if ($satuan && $satuan->tipe === 'bulat' && floor($value) != $value) {
                    $fail("Nilai harus bilangan bulat untuk satuan {$satuan->nama} (tidak boleh desimal).");
                }
            };

            $validated = $request->validate([
                'nama_bahan'   => 'required|string|max:100|unique:bahan_bakus,nama_bahan',
                'satuan_id'    => 'required|exists:satuans,id',
                'stok'         => ['required', 'numeric', 'min:0', $aturanBulat],
                'stok_minimum' => ['required', 'numeric', 'min:0', $aturanBulat],
            ]);

            $bahanBaku = BahanBaku::create($validated);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($bahanBaku)
                ->withProperties([
                    'nama_bahan'   => $bahanBaku->nama_bahan,
                    'satuan'       => $bahanBaku->satuan->nama,
                    'stok'         => $bahanBaku->stok,
                    'stok_minimum' => $bahanBaku->stok_minimum,
                ])
                ->log('menambahkan bahan baku');

            return redirect()
                ->route('bahan-baku.index')
                ->with('success', 'Bahan baku berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(BahanBaku $bahanBaku)
    {
        try {
            $satuan = Satuan::orderBy('nama')->get();

            return view(
                'pages.bahan-baku.edit',
                compact('bahanBaku', 'satuan')
            );
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, BahanBaku $bahan_baku)
    {
        try {
            $satuan = Satuan::find($request->satuan_id);

            $aturanBulat = function ($attribute, $value, $fail) use ($satuan) {
                if ($satuan && $satuan->tipe === 'bulat' && floor($value) != $value) {
                    $fail("Nilai harus bilangan bulat untuk satuan {$satuan->nama} (tidak boleh desimal).");
                }
            };

            $validated = $request->validate([
                'nama_bahan' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('bahan_bakus', 'nama_bahan')
                        ->ignore($bahan_baku->id),
                ],
                'satuan_id'    => 'required|exists:satuans,id',
                'stok'         => ['required', 'numeric', 'min:0', $aturanBulat],
                'stok_minimum' => ['required', 'numeric', 'min:0', $aturanBulat],
            ]);

            $bahan_baku->update($validated);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($bahan_baku)
                ->withProperties([
                    'nama_bahan'   => $bahan_baku->nama_bahan,
                    'satuan'       => $bahan_baku->fresh()->satuan->nama,
                    'stok'         => $bahan_baku->stok,
                    'stok_minimum' => $bahan_baku->stok_minimum,
                ])
                ->log('memperbarui bahan baku');

            return redirect()
                ->route('bahan-baku.index')
                ->with('success', 'Bahan baku berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(BahanBaku $bahan_baku)
    {
        try {
            if ($bahan_baku->transaksi()->exists() || $bahan_baku->detailProduksi()->exists()) {
                return redirect()
                    ->route('bahan-baku.index')
                    ->with('error', "Bahan baku \"{$bahan_baku->nama_bahan}\" tidak bisa dihapus karena sudah punya riwayat transaksi/produksi.");
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($bahan_baku)
                ->withProperties([
                    'nama_bahan'   => $bahan_baku->nama_bahan,
                    'satuan'       => $bahan_baku->satuan->nama,
                    'stok'         => $bahan_baku->stok,
                    'stok_minimum' => $bahan_baku->stok_minimum,
                ])
                ->log('menghapus bahan baku');

            $bahan_baku->delete();

            return redirect()
                ->route('bahan-baku.index')
                ->with('success', 'Bahan baku berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}