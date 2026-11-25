<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberStamp;
use App\Models\PengaturanMember;
use App\Models\StampProgram;
use App\Models\StampRedemption;
use App\Models\Transaksi;
use App\Support\ActivityLogHelper;
use App\Support\NotificationHelper;
use App\Support\PembatalanPesananPickup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PesananPickupController extends Controller
{
    public function indeks(Request $request)
    {
        $statusPesanan = $request->query('status_pesanan', 'semua');
        $statusPembayaran = $request->query('status_pembayaran', 'semua');
        $cari = $request->query('cari');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');

        $query = Transaksi::with(['detailTransaksi', 'member'])
            ->where('asal_pesanan', Transaksi::ASAL_MEMBER_APP);

        if ($statusPesanan !== 'semua') {
            $query->where('status_pesanan', $statusPesanan);
        }

        if ($statusPembayaran === 'lunas') {
            $query->where('status_pembayaran', 'Lunas');
        } elseif ($statusPembayaran === 'menunggu_konfirmasi') {
            $query->whereNotNull('bukti_dikonfirmasi_pada')->where('status_pembayaran', '!=', 'Lunas');
        } elseif ($statusPembayaran === 'menunggu_pembayaran') {
            $query->whereNull('bukti_dikonfirmasi_pada')->where('status_pembayaran', '!=', 'Lunas');
        }

        if ($cari) {
            $query->where(function ($q) use ($cari) {
                $q->where('kode_transaksi', 'like', "%{$cari}%")
                  ->orWhere('kode_pickup', 'like', "%{$cari}%")
                  ->orWhere('nama_pembeli', 'like', "%{$cari}%");
            });
        }

        if ($tanggalMulai) {
            $query->whereDate('created_at', '>=', $tanggalMulai);
        }
        if ($tanggalSelesai) {
            if ($tanggalMulai && $tanggalSelesai < $tanggalMulai) {
                $tanggalSelesai = $tanggalMulai;
            }
            $query->whereDate('created_at', '<=', $tanggalSelesai);
        }

        if ($request->ajax()) {
            try {
                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('kode_html', fn ($item) => view('admin.pesanan-pickup.partials.kode', compact('item'))->render())
                    ->addColumn('kode_pesanan_html', fn ($item) => view('admin.pesanan-pickup.partials.kode-pesanan', compact('item'))->render())
                    ->addColumn('pembeli_html', fn ($item) => view('admin.pesanan-pickup.partials.pembeli', compact('item'))->render())
                    ->editColumn('total_bayar', fn ($item) => 'Rp ' . number_format($item->total_bayar, 0, ',', '.'))
                    ->editColumn('created_at', fn ($item) => $item->created_at->translatedFormat('d M Y, H:i'))
                    ->addColumn('status_pesanan_html', fn ($item) => view('admin.pesanan-pickup.partials.status-pesanan', compact('item'))->render())
                    ->addColumn('status_pembayaran_html', fn ($item) => view('admin.pesanan-pickup.partials.status-pembayaran', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('admin.pesanan-pickup.partials.aksi', compact('item'))->render())
                    ->rawColumns(['kode_html', 'kode_pesanan_html', 'pembeli_html', 'status_pesanan_html', 'status_pembayaran_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data pesanan.'], 500);
            }
        }

        try {
            return view('admin.pesanan-pickup.index', [
                'statusPesananFilter' => $statusPesanan,
                'statusPembayaranFilter' => $statusPembayaran,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * JSON API untuk modal detail (pola sama seperti LaporanController::detailTransaksi).
     */
    public function detail(Transaksi $transaksi)
    {
        try {
            abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

            return response()->json([
                'status' => 'success',
                'data' => $transaksi->load(['detailTransaksi.produk', 'member']),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Kasir mengonfirmasi bukti transfer/QRIS yang sudah dikunci member
     * sebagai valid. Menandai pesanan Lunas.
     */
    public function konfirmasiPembayaran(Transaksi $transaksi)
    {
        try {
            abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

            if ($transaksi->status_pembayaran === 'Lunas') {
                return back()->with('error', 'Pesanan ini sudah lunas.');
            }
            if (! $transaksi->bukti_dikonfirmasi_pada) {
                return back()->with('error', 'Member belum mengonfirmasi bukti pembayaran.');
            }

            $transaksi->update([
                'status_pembayaran' => 'Lunas',
                'jumlah_diterima' => $transaksi->total_bayar,
                'kembalian' => 0,
                // Pembayaran online yang dikonfirmasi ikut tercatat di shift yang sedang berjalan
                // (hanya satu shift boleh berjalan), siapa pun yang menekan konfirmasi.
                'shift_kasir_id' => \App\Models\ShiftKasir::idBerjalanSaatIni(),
            ]);

            activity()->causedBy(auth()->user())->performedOn($transaksi)
                ->log('mengonfirmasi bukti pembayaran pesanan pick up');

            \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'update', 'lunas')));

            return back()->with('success', "Pembayaran pesanan {$transaksi->kode_transaksi} dikonfirmasi.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tolakPembayaran(Request $request, Transaksi $transaksi)
    {
        try {
            abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

            $validated = $request->validate([
                'alasan' => 'required|string|max:255',
            ], [
                'alasan.required' => 'Alasan penolakan wajib diisi.',
                'alasan.max' => 'Alasan penolakan maksimal 255 karakter.',
            ]);

            if ($transaksi->status_pembayaran === 'Lunas') {
                return back()->with('error', 'Pesanan ini sudah lunas, tidak bisa ditolak.');
            }

            if ($transaksi->kode_transaksi) {
                Storage::disk('public')->deleteDirectory('bukti-pembayaran/' . $transaksi->kode_transaksi);
            } elseif ($transaksi->bukti_pembayaran) {
                Storage::disk('public')->delete($transaksi->bukti_pembayaran);
            }

            $transaksi->update([
                'bukti_pembayaran' => null,
                'bukti_dikonfirmasi_pada' => null,
                'catatan_penolakan' => trim($validated['alasan']),
                // Member diminta upload ulang: kasih waktu baru supaya tidak langsung
                // dibatalkan otomatis kalau batas waktu awalnya sudah lewat.
                'batas_bayar_pada' => $transaksi->batas_bayar_pada
                    ? now()->addMinutes(Transaksi::BATAS_BAYAR_MENIT)
                    : null,
            ]);

            activity()->causedBy(auth()->user())->performedOn($transaksi)
                ->log('menolak bukti pembayaran pesanan pick up');

            \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'update', 'tolak_bukti')));

            return back()->with('success', "Bukti pembayaran {$transaksi->kode_transaksi} ditolak, member diminta upload ulang.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function prosesPesanan(Transaksi $transaksi)
    {
        try {
            abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

            if ($transaksi->status_pembayaran !== 'Lunas') {
                return back()->with('error', 'Pembayaran belum dikonfirmasi.');
            }
            if ($transaksi->diproses_pada) {
                return back()->with('error', 'Pesanan ini sudah diproses.');
            }

            DB::beginTransaction();
            try {
                $transaksi->load('detailTransaksi');

                // Pesanan baru: stok sudah dikurangi saat checkout (stok_dikurangi_pada terisi),
                // jadi di sini tidak ada lagi pengurangan stok. Pesanan lama yang dibuat sebelum
                // perubahan ini masih memakai alur lama (stok dikurangi saat diproses).
                if (! $transaksi->stok_dikurangi_pada) {
                    $this->kurangiStokPesananLama($transaksi);
                }

                $transaksi->update(['diproses_pada' => now()]);

                DB::commit();

                activity()->causedBy(auth()->user())->performedOn($transaksi)
                    ->log('memproses pesanan pick up');

                \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'update', 'diproses')));

                return back()->with('success', "Pesanan {$transaksi->kode_transaksi} mulai diproses.");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error proses pesanan pickup: ' . $e->getMessage());
                return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tandaiSiap(Transaksi $transaksi)
    {
        try {
            abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

            if ($transaksi->status_pesanan !== Transaksi::STATUS_DIPROSES) {
                return back()->with('error', 'Pesanan ini bukan berstatus Diproses.');
            }
            if ($transaksi->status_pembayaran !== 'Lunas' || ! $transaksi->diproses_pada) {
                return back()->with('error', 'Selesaikan konfirmasi pembayaran & proses pesanan dulu.');
            }

            $transaksi->update([
                'status_pesanan' => Transaksi::STATUS_SIAP_DIAMBIL,
                'siap_pada' => now(),
                'kode_pickup' => $transaksi->kode_pickup ?: Transaksi::buatKodePickupUnik(),
            ]);

            activity()->causedBy(auth()->user())->performedOn($transaksi)
                ->log('menandai pesanan pick up siap diambil');

            \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'update', 'siap')));

            return back()->with('success', "Pesanan {$transaksi->kode_transaksi} ditandai siap diambil.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function batalkan(Request $request, Transaksi $transaksi)
    {
        try {
            abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

            $validated = $request->validate([
                'alasan' => 'required|string|max:255',
            ], [
                'alasan.required' => 'Alasan pembatalan wajib diisi.',
                'alasan.max' => 'Alasan pembatalan maksimal 255 karakter.',
            ]);

            if (in_array($transaksi->status_pesanan, [Transaksi::STATUS_SUDAH_DIAMBIL, Transaksi::STATUS_DIBATALKAN], true)) {
                return back()->with('error', 'Pesanan ini sudah tidak bisa dibatalkan.');
            }

            try {
                // Stok, voucher & hadiah stamp dikembalikan di PembatalanPesananPickup
                // (logika yang sama dipakai saat sistem membatalkan otomatis karena telat bayar).
                $jumlahRewardDikembalikan = PembatalanPesananPickup::batalkan($transaksi, $validated['alasan']);

                activity()->causedBy(auth()->user())->performedOn($transaksi)
                    ->log('membatalkan pesanan pick up dari member app (alasan: ' . trim($validated['alasan']) . ')' . ($jumlahRewardDikembalikan > 0 ? " ($jumlahRewardDikembalikan hadiah stamp dikembalikan ke status menunggu diambil)" : ''));

                \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'update', 'batal')));

                $pesanSukses = "Pesanan {$transaksi->kode_transaksi} dibatalkan, stok & voucher dikembalikan.";
                if ($jumlahRewardDikembalikan > 0) {
                    $pesanSukses = "Pesanan {$transaksi->kode_transaksi} dibatalkan. Stok/voucher dikembalikan & hadiah stamp siap diambil kembali oleh member.";
                }

                return back()->with('success', $pesanSukses);
            } catch (\Exception $e) {
                Log::error('Error batalkan pesanan pickup: ' . $e->getMessage());
                return back()->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tandaiSudahDiambil(Request $request, Transaksi $transaksi)
    {
        try {
            abort_unless($transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP, 404);

            if ($transaksi->status_pesanan !== Transaksi::STATUS_SIAP_DIAMBIL) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan ini belum siap diambil.',
                ], 422);
            }

            $validated = $request->validate([
                'kode_pickup' => 'required|alpha_num|max:6',
            ]);

            $kodeInput = strtoupper(trim($validated['kode_pickup']));
            if (! $transaksi->kode_pickup || $kodeInput !== $transaksi->kode_pickup) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode pickup tidak sesuai. Minta pembeli menunjukkan kode dari aplikasi member.',
                ], 422);
            }

            DB::beginTransaction();
            try {
                $transaksi->update([
                    'status_pesanan' => Transaksi::STATUS_SUDAH_DIAMBIL,
                    'diambil_pada' => now(),
                ]);

                $this->berikanPoinDanStamp($transaksi);

                DB::commit();

                activity()->causedBy(auth()->user())->performedOn($transaksi)
                    ->log('menyerahkan pesanan pick up');

                \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'update', 'selesai')));

                return response()->json([
                    'status' => 'success',
                    'message' => "Pesanan {$transaksi->kode_transaksi} berhasil diserahkan.",
                    'data' => $transaksi->fresh()->load(['detailTransaksi.produk', 'member']),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error tandai sudah diambil: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menandai sudah diambil: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function berikanPoinDanStamp(Transaksi $transaksi): void
    {
        if (! $transaksi->member_id) {
            return;
        }

        $pengaturan = PengaturanMember::aktifSekarang();
        if ($pengaturan) {
            $nominalUntukPoin = $transaksi->subtotal + $transaksi->pajak;
            $kelipatan = floor($nominalUntukPoin / $pengaturan->minimal_belanja);
            $poin = $kelipatan * $pengaturan->poin;

            $member = Member::find($transaksi->member_id);
            if ($member) {
                $poinSebelum = $member->poin;
                if ($poin > 0) {
                    $member->increment('poin', $poin);
                }
                $transaksi->update([
                    'poin_sebelum' => $poinSebelum,
                    'poin_didapat' => (int) $poin,
                ]);
            }
        }

        $stampProgram = StampProgram::aktifSekarang();
        if ($stampProgram) {
            // Stamp dihitung dari total akhir (setelah diskon), maksimal 1 per transaksi.
            $stampDidapat = $stampProgram->hitungStampDidapat($transaksi->total_bayar);

            if ($stampDidapat > 0) {
                $memberStamp = MemberStamp::firstOrCreate(
                    ['member_id' => $transaksi->member_id, 'stamp_program_id' => $stampProgram->id],
                    ['jumlah_stamp' => 0]
                );

                $stampSebelum = $memberStamp->jumlah_stamp;
                $stampBaru = min($stampSebelum + $stampDidapat, $stampProgram->jumlah_stamp_dibutuhkan);

                $memberStamp->update(['jumlah_stamp' => $stampBaru]);

                $transaksi->update([
                    'stamp_program_id' => $stampProgram->id,
                    'stamp_sebelum' => $stampSebelum,
                    'stamp_didapat' => $stampBaru - $stampSebelum,
                ]);
            }
        }
    }

    /**
     * Alur lama (sebelum stok dikurangi saat checkout): menentukan butuh_produksi dari stok terbaru
     * dan mengurangi stok saat pesanan diproses. Hanya dipakai untuk pesanan lama yang belum
     * punya penanda stok_dikurangi_pada.
     */
    private function kurangiStokPesananLama(Transaksi $transaksi): void
    {
        foreach ($transaksi->detailTransaksi as $detail) {
            if (! $detail->produk_id) {
                continue;
            }

            // Lewati baris komponen bundling/promo (harga 0) — stoknya sudah
            // diurus lewat pecahan saat checkout; di sini kita hanya perlu
            // meng-update butuh_produksi + kurangi stok untuk baris reguler.
            $isBundlingKomponen = $detail->bundling_id && $detail->harga_satuan == 0;
            $isPromoKomponen = $detail->promo_event_id && $detail->harga_satuan == 0;
            if ($isBundlingKomponen || $isPromoKomponen) {
                // Komponen: stok sudah dihitung via pecahJumlahProduksi
                // saat checkout, butuh_produksi sudah diset.
                // Kurangi stok untuk yang tidak butuh_produksi.
                if (! $detail->butuh_produksi) {
                    if ($detail->detail_produk_id) {
                        $varian = \App\Models\DetailProduk::lockForUpdate()->find($detail->detail_produk_id);
                        if ($varian) {
                            $varian->stok = max(0, (int) $varian->stok - (int) $detail->jumlah);
                            $varian->status_ketersediaan = $varian->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
                            $varian->save();
                            $pInduk = \App\Models\Produk::find($varian->produk_id);
                            if ($pInduk) {
                                $this->sinkronStokProduk($pInduk);
                            }
                        }
                        continue;
                    }

                    $produk = \App\Models\Produk::lockForUpdate()->find($detail->produk_id);
                    if ($produk) {
                        try {
                            if ($detail->varian_rasa && $produk->punya_varian_rasa) {
                                $produk->ubahStokRasa($detail->ukuran, $detail->varian_rasa, -$detail->jumlah);
                            } else {
                                $produk->decrementStokUkuran($detail->ukuran, $detail->jumlah);
                            }
                        } catch (\Throwable $e) {
                            Log::error('Gagal mengurangi stok komponen saat proses pesanan pickup', [
                                'detail_id' => $detail->id,
                                'produk_id' => $produk->id,
                                'error' => $e->getMessage(),
                            ]);
                            throw $e;
                        }
                    }
                }
                continue;
            }

            $produk = \App\Models\Produk::lockForUpdate()->find($detail->produk_id);
            if (! $produk) {
                continue;
            }

            // Tentukan apakah perlu diproduksi untuk varian baru
            if ($detail->detail_produk_id) {
                $varian = \App\Models\DetailProduk::lockForUpdate()->find($detail->detail_produk_id);
                if ($varian) {
                    $stokTersedia = max(0, (int) $varian->stok);
                    if ($varian->perlu_diproduksi || $stokTersedia <= 0) {
                        $detail->butuh_produksi = true;
                        $detail->save();
                    } elseif ($stokTersedia >= $detail->jumlah) {
                        $detail->butuh_produksi = false;
                        $detail->save();
                        $varian->stok = $stokTersedia - $detail->jumlah;
                        $varian->status_ketersediaan = $varian->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
                        $varian->save();
                        $this->sinkronStokProduk($produk);
                    } else {
                        $sisaProduksi = $detail->jumlah - $stokTersedia;
                        $detail->jumlah = $stokTersedia;
                        $detail->subtotal = $detail->harga_satuan * $stokTersedia;
                        $detail->butuh_produksi = false;
                        $detail->save();

                        \App\Models\DetailTransaksi::create([
                            'transaksi_id' => $transaksi->id,
                            'produk_id' => $produk->id,
                            'detail_produk_id' => $varian->id,
                            'nama_produk' => $detail->nama_produk,
                            'ukuran' => $varian->ukuran,
                            'varian_rasa' => $varian->variasi,
                            'harga_satuan' => $detail->harga_satuan,
                            'jumlah' => $sisaProduksi,
                            'catatan' => $detail->catatan,
                            'subtotal' => $detail->harga_satuan * $sisaProduksi,
                            'butuh_produksi' => true,
                        ]);

                        $varian->stok = 0;
                        $varian->status_ketersediaan = 'perlu_diproduksi';
                        $varian->save();
                        $this->sinkronStokProduk($produk);
                    }
                    continue;
                }
            }

            // Tentukan apakah perlu diproduksi (sama seperti kasir)
            if ($produk->status_ketersediaan === 'perlu_diproduksi') {
                $detail->butuh_produksi = true;
                $detail->save();
                continue;
            }

            // Stok dibaca FRESH dari DB (produk di-find ulang per baris, dan
            // stok sudah dikurangi oleh baris sebelumnya). Jangan dikurangi
            // lagi dengan "stok terpakai" — itu bikin stok dihitung dobel
            // dan baris yang sebenarnya masih ready malah jadi perlu produksi.
            $stokTersedia = max(0, (int) floor((float) $produk->stokUntukUkuranRasa($detail->ukuran, $detail->varian_rasa)));

            if ($stokTersedia >= $detail->jumlah) {
                // Stok cukup: kurangi stok, tidak perlu produksi
                $detail->butuh_produksi = false;
                $detail->save();

                try {
                    if ($detail->varian_rasa && $produk->punya_varian_rasa) {
                        $produk->ubahStokRasa($detail->ukuran, $detail->varian_rasa, -$detail->jumlah);
                    } else {
                        $produk->decrementStokUkuran($detail->ukuran, $detail->jumlah);
                    }
                } catch (\Throwable $e) {
                    Log::error('Gagal mengurangi stok saat proses pesanan pickup', [
                        'produk_id' => $produk->id,
                        'ukuran' => $detail->ukuran,
                        'varian_rasa' => $detail->varian_rasa,
                        'jumlah' => $detail->jumlah,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            } elseif ($stokTersedia <= 0) {
                // Stok habis: semua perlu diproduksi
                $detail->butuh_produksi = true;
                $detail->save();
            } else {
                // Stok sebagian: pecah jadi 2 baris (ready + produksi)
                $sisaProduksi = $detail->jumlah - $stokTersedia;

                // Update baris ini jadi porsi ready
                $detail->jumlah = $stokTersedia;
                $detail->subtotal = $detail->harga_satuan * $stokTersedia;
                $detail->butuh_produksi = false;
                $detail->save();

                // Buat baris baru untuk porsi produksi
                \App\Models\DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $detail->nama_produk,
                    'ukuran' => $detail->ukuran,
                    'varian_rasa' => $detail->varian_rasa,
                    'harga_satuan' => $detail->harga_satuan,
                    'jumlah' => $sisaProduksi,
                    'catatan' => $detail->catatan,
                    'subtotal' => $detail->harga_satuan * $sisaProduksi,
                    'butuh_produksi' => true,
                ]);


                try {
                    if ($detail->varian_rasa && $produk->punya_varian_rasa) {
                        $produk->ubahStokRasa($detail->ukuran, $detail->varian_rasa, -$stokTersedia);
                    } else {
                        $produk->decrementStokUkuran($detail->ukuran, $stokTersedia);
                    }
                } catch (\Throwable $e) {
                    Log::error('Gagal mengurangi stok (pecahan) saat proses pesanan pickup', [
                        'produk_id' => $produk->id,
                        'ukuran' => $detail->ukuran,
                        'varian_rasa' => $detail->varian_rasa,
                        'jumlah_ready' => $stokTersedia,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        }
    }

    /** Stok & status produk dihitung dari detail produk, jadi cukup menyegarkan cache stoknya. */
    private function sinkronStokProduk(\App\Models\Produk $produk): void
    {
        $produk->segarkanStok();
    }
}