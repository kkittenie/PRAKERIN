<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\TransaksiBahanBaku;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiBahanBakuController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = TransaksiBahanBaku::with('bahanBaku.satuan');

                // Filter rentang tanggal (opsional). Kalau salah satu kosong,
                // filter berlaku satu sisi saja (dari / sampai tanggal itu).
                $dari = $this->parseTanggal($request->input('tanggal_dari'));
                $sampai = $this->parseTanggal($request->input('tanggal_sampai'));

                if ($dari && $sampai && $dari->gt($sampai)) {
                    [$dari, $sampai] = [$sampai, $dari];
                }

                $query->when($dari, fn($q) => $q->whereDate('tanggal', '>=', $dari->toDateString()))
                    ->when($sampai, fn($q) => $q->whereDate('tanggal', '<=', $sampai->toDateString()));

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('tanggal', fn($item) => \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y'))
                    ->addColumn('bahan_baku_html', fn($item) => view('pages.transaksi-bahan-baku.partials.bahan-baku', compact('item'))->render())
                    ->addColumn('jumlah_html', fn($item) => view('pages.transaksi-bahan-baku.partials.jumlah', compact('item'))->render())
                    ->addColumn('total_harga_html', fn($item) => view('pages.transaksi-bahan-baku.partials.total-harga', compact('item'))->render())
                    ->editColumn('supplier', fn($item) => $item->supplier ?: '-')
                    ->addColumn('aksi', fn($item) => view('pages.transaksi-bahan-baku.partials.aksi', compact('item'))->render())
                    ->orderColumn('tanggal', function ($query, $order) {
                        // Sekunder sort by id, sama seperti perilaku lama
                        // (->latest('tanggal')->latest('id')).
                        $query->orderBy('tanggal', $order)->orderBy('id', $order);
                    })
                    ->orderColumn('bahan_baku_html', function ($query, $order) {
                        // 'bahan_baku_html' bukan kolom di tabel transaksi_bahan_bakus,
                        // melainkan relasi ke bahan_bakus.nama_bahan, jadi di-join manual.
                        $query->join('bahan_bakus', 'transaksi_bahan_bakus.bahan_baku_id', '=', 'bahan_bakus.id')
                            ->orderBy('bahan_bakus.nama_bahan', $order)
                            ->select('transaksi_bahan_bakus.*');
                    })
                    ->filter(function ($query) use ($request) {
                        // Pencarian hanya berdasarkan nama bahan baku.
                        // Parameter kedua = false supaya pencarian global bawaan
                        // DataTables (yang bikin hasil kosong / error) tidak ikut jalan.
                        $search = trim((string) $request->input('search.value'));

                        if ($search !== '') {
                            $query->whereHas('bahanBaku', function ($qb) use ($search) {
                                $qb->where('nama_bahan', 'like', "%{$search}%");
                            });
                        }
                    }, false)
                    ->rawColumns(['bahan_baku_html', 'jumlah_html', 'total_harga_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data transaksi bahan baku.'], 500);
            }
        }

        return view('pages.transaksi-bahan-baku.index');
    }

    private function parseTanggal($value): ?\Carbon\Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function create()
    {
        try {
            $bahanBaku = BahanBaku::with('satuan')->orderBy(
                'nama_bahan'
            )->get();

            return view(
                'pages.transaksi-bahan-baku.create',
                compact('bahanBaku')
            );
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(
        TransaksiBahanBaku $transaksiBahanBaku
    ) {
        try {
            $transaksiBahanBaku->load([
                'bahanBaku',
                'user',
            ]);

            return view(
                'pages.transaksi-bahan-baku.show',
                compact('transaksiBahanBaku')
            );
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}