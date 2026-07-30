<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\TransaksiBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiBahanBakuController extends Controller
{
    public function index(Request $request)
    {
        $transaksi = TransaksiBahanBaku::with([
            'bahanBaku',
            'user',
        ])
        ->when($request->search, function ($query) use ($request) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'supplier',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'nomor_nota',
                    'like',
                    "%{$search}%"
                )
                ->orWhereHas(
                    'bahanBaku',
                    function ($q) use ($search) {

                        $q->where(
                            'nama_bahan',
                            'like',
                            "%{$search}%"
                        );

                    }
                );

            });

        })
        ->latest('tanggal')
        ->latest('id')
        ->paginate(10)
        ->withQueryString();

        return view(
            'pages.transaksi-bahan-baku.index',
            compact('transaksi')
        );
    }


    public function create()
    {
        $bahanBaku = BahanBaku::with('satuan')->orderBy(
            'nama_bahan'
        )->get();

        return view(
            'pages.transaksi-bahan-baku.create',
            compact('bahanBaku')
        );
    }


    public function store(Request $request)
    {
        $bahanBaku = \App\Models\BahanBaku::with('satuan')->find($request->bahan_baku_id);

        $validated = $request->validate([

            'tanggal' => [
                'required',
                'date',
            ],

            'bahan_baku_id' => [
                'required',
                'exists:bahan_bakus,id',
            ],

            'jumlah' => [
                'required',
                'numeric',
                'gt:0',
                function ($attribute, $value, $fail) use ($bahanBaku) {
                    if ($bahanBaku && $bahanBaku->satuan?->tipe === 'bulat' && floor($value) != $value) {
                        $fail("Jumlah harus bilangan bulat untuk satuan {$bahanBaku->satuan->nama} (tidak boleh desimal).");
                    }
                },
            ],

            'harga_satuan' => [
                'required',
                'numeric',
                'min:0',
            ],

            'supplier' => [
                'nullable',
                'string',
                'max:255',
            ],

            'nomor_nota' => [
                'nullable',
                'string',
                'max:100',
            ],

            'keterangan' => [
                'nullable',
                'string',
            ],

        ]);


        DB::transaction(function () use ($validated) {

            $bahanBaku = BahanBaku::lockForUpdate()
                ->findOrFail(
                    $validated['bahan_baku_id']
                );

            $totalHarga =
                $validated['jumlah']
                *
                $validated['harga_satuan'];


            TransaksiBahanBaku::create([

                'tanggal' =>
                    $validated['tanggal'],

                'bahan_baku_id' =>
                    $validated['bahan_baku_id'],

                'jumlah' =>
                    $validated['jumlah'],

                'harga_satuan' =>
                    $validated['harga_satuan'],

                'total_harga' =>
                    $totalHarga,

                'supplier' =>
                    $validated['supplier'] ?? null,

                'nomor_nota' =>
                    $validated['nomor_nota'] ?? null,

                'user_id' =>
                    Auth::id(),

                'keterangan' =>
                    $validated['keterangan'] ?? null,

            ]);


            $bahanBaku->increment(
                'stok',
                $validated['jumlah']
            );

        });


        return redirect()
            ->route(
                'transaksi-bahan-baku.index'
            )
            ->with(
                'success',
                'Transaksi bahan baku berhasil ditambahkan.'
            );
    }


    public function show(
        TransaksiBahanBaku $transaksiBahanBaku
    ) {
        $transaksiBahanBaku->load([
            'bahanBaku',
            'user',
        ]);

        return view(
            'pages.transaksi-bahan-baku.show',
            compact('transaksiBahanBaku')
        );
    }
}