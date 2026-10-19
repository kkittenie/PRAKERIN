<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\DetailProduk;
use App\Models\PesananDitahan;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PengaturanMember;
use App\Models\Member;
use App\Models\Bundling;
use App\Models\PromoProdukEvent;
use App\Models\StampProgram;
use App\Models\MemberStamp;
use App\Models\StampRedemption;
use App\Models\PenukaranPoin;
use App\Models\PengaturanPajak;
use App\Models\QrisImage;
use App\Models\RewardKatalog;
use App\Models\VoucherUsage;

class TransaksiKasirController extends Controller
{
    private function validasiVoucherUntukTransaksi(?int $voucherId, ?int $memberId): ?RewardKatalog
    {
        if (!$voucherId) {
            return null;
        }

        if (!$memberId) {
            throw new \Exception('Voucher hanya bisa dipakai jika member sudah dipilih.');
        }

        $member = Member::find($memberId);
        if (!$member) {
            throw new \Exception('Member tidak ditemukan.');
        }

        $voucher = RewardKatalog::with('produks')
            ->where('id', $voucherId)
            ->where('jenis', 'voucher')
            ->lockForUpdate()
            ->first();

        if (!$voucher) {
            throw new \Exception('Voucher tidak ditemukan.');
        }

        if (!$voucher->status_aktif) {
            throw new \Exception('Voucher ini sedang tidak aktif.');
        }

        if ($voucher->statusBelumMulai()) {
            throw new \Exception('Voucher ini belum mulai berlaku.');
        }

        if ($voucher->statusKedaluwarsa()) {
            throw new \Exception('Voucher ini sudah kedaluwarsa.');
        }

        if (!$voucher->berhakDigunakan($member)) {
            throw new \Exception('Member tidak memiliki akses atau telah mencapai batas penggunaan voucher ini.');
        }

        return $voucher;
    }

    private function tandaiVoucherDigunakan(RewardKatalog $voucher, Transaksi $transaksi): void
    {
        VoucherUsage::firstOrCreate(
            [
                'transaksi_id' => $transaksi->id,
            ],
            [
                'reward_katalog_id' => $voucher->id,
                'member_id' => $transaksi->member_id,
                'used_at' => now(),
            ]
        );
    }
    private function validasiRewardStampUntukTransaksi(?int $stampRedemptionId, ?int $memberId): ?StampRedemption
    {
        if (!$stampRedemptionId) {
            return null;
        }

        if (!$memberId) {
            throw new \Exception('Reward stamp hanya bisa dipakai jika member sudah dipilih.');
        }

        $redemption = StampRedemption::with(['produk', 'detailProduk'])
            ->where('id', $stampRedemptionId)
            ->lockForUpdate()
            ->first();

        if (!$redemption) {
            throw new \Exception('Reward stamp tidak ditemukan.');
        }

        if ((int) $redemption->member_id !== (int) $memberId) {
            throw new \Exception('Reward stamp ini bukan milik member yang dipilih.');
        }

        if ($redemption->status !== 'menunggu_diambil') {
            throw new \Exception('Reward stamp ini sudah pernah diambil atau dibatalkan.');
        }

        if (!$redemption->produk) {
            throw new \Exception('Produk hadiah reward stamp ini tidak ditemukan.');
        }

        return $redemption;
    }

    private function prosesRewardStampTransaksi(Transaksi $transaksi, ?StampRedemption $redemption): void
    {
        if (!$redemption) {
            return;
        }

        $produk = $redemption->produk;
        $detail = $redemption->detailProduk;

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => $produk->id,
            'detail_produk_id' => $detail?->id,
            'nama_produk' => $produk->nama_produk,
            'ukuran' => $detail?->ukuran,
            'varian_rasa' => $detail?->variasi,
            'harga_satuan' => 0,
            'jumlah' => $redemption->jumlah ?: 1,
            'catatan' => 'Reward Stamp',
            'subtotal' => 0,
            // Hadiah varian tertentu sudah dipotong stoknya saat ditukar, jadi tidak perlu produksi.
            'butuh_produksi' => $detail ? false : $this->butuhProduksi($produk),
        ]);

        $redemption->update([
            'status' => 'selesai',
            'diambil_pada' => now(),
            'transaksi_id' => $transaksi->id,
            'diproses_oleh' => auth()->id(),
        ]);
    }
    private function kembalikanRewardStampDariTransaksi(Transaksi $transaksi): void
    {
        StampRedemption::where('transaksi_id', $transaksi->id)->update([
            'status' => 'menunggu_diambil',
            'diambil_pada' => null,
            'transaksi_id' => null,
            'diproses_oleh' => null,
        ]);
    }

    private function butuhProduksi(Produk $produk): bool
    {
        // Produk dengan varian baru: perlu produksi kalau SEMUA varian aktif habis.
        if ($produk->detailProduks()->exists()) {
            return !$produk->detailProduks()->where('status_aktif', true)->where('stok', '>', 0)->exists();
        }

        // Kalau produk punya varian ukuran, dianggap perlu produksi hanya
        // kalau SEMUA ukuran (Besar & Kecil) sedang habis.
        return $produk->habisSemuaUkuran();
    }

    private function tentukanPerluProduksi(Produk $produk, array $item): bool
    {
        if ($produk->status_ketersediaan === 'perlu_diproduksi') {
            return true;
        }

        // Cek stok per kombinasi (ukuran + rasa) kalau keduanya ada,
        // fallback ke stok ukuran saja kalau tidak ada rasa.
        $varianRasa = $item['varian_rasa'] ?? null;
        return (bool) ($item['butuh_produksi'] ?? ($produk->stokUntukUkuranRasa($item['ukuran'] ?? null, $varianRasa) <= 0));
    }

    public function indeks()
    {
        try {
            $daftarProduk = Produk::with(['kategori', 'promos.detailProduks', 'detailProduks', 'varianProduks'])
                ->where('status_aktif', true)
                ->dijualSatuan()
                ->get()
                ->each(function (Produk $produk) {
                    // Detail produk (tabel detail_produks) yang dipakai kasir.
                    $produk->setAttribute('punya_varian', $produk->punya_varian);
                    $produk->setAttribute('daftar_varian', $produk->daftarVarianKasir());
                });

            $daftarKategori = KategoriProduk::all();

            $daftarBundling = Bundling::with([
                'items.produk.detailProduks',
                'items.detailProduk',
            ])
                ->where('status_aktif', true)
                ->get()
                ->map(function ($bundling) {
                    // Bundling bebas: kasir memilih rasa sendiri, jadi stok = total stok semua rasa
                    // aktif dari produk + ukuran yang ditentukan admin, dibagi total isi per paket.
                    // Daftar rasa tiap baris isi dikirim ke JS lewat 'opsi_rasa'.
                    if ($bundling->isBebas()) {
                        $bundling->stok_bundling = intdiv($bundling->stokTotalBebas(), max(1, $bundling->targetQtyBebas()));
                        $bundling->perlu_diproduksi = $bundling->stok_bundling <= 0;

                        $bundling->items->each(function ($it) {
                            $it->setAttribute('opsi_rasa', $it->opsiRasaData());
                            // Detail produk lengkap (harga modal dll.) tidak perlu ikut ke browser kasir.
                            $it->produk?->makeHidden('detailProduks');
                        });

                        return $bundling;
                    }

                    $bundling->items->each(fn ($it) => $it->produk?->makeHidden('detailProduks'));

                    $stokMaksimal = $bundling->items->reduce(function ($carry, $item) {
                        if (!$item->produk) {
                            return 0;
                        }
                        if ($item->detailProduk) {
                            $stokVarian = (int) $item->detailProduk->stok;
                            $bisaDibuat = intdiv($stokVarian, max(1, $item->qty));
                            return is_null($carry) ? $bisaDibuat : min($carry, $bisaDibuat);
                        }
                        // Stok dihitung sesuai ukuran + rasa yang dipilih untuk
                        // item ini (kalau produknya punya varian ukuran / rasa),
                        // bukan stok gabungan semua ukuran / rasa.
                        $stokUkuran = $item->produk->stokUntukUkuranRasa($item->ukuran, $item->varian_rasa);
                        $bisaDibuat = intdiv((int) $stokUkuran, max(1, $item->qty));
                        return is_null($carry) ? $bisaDibuat : min($carry, $bisaDibuat);
                    }, null) ?? 0;

                    $bundling->stok_bundling = $stokMaksimal;
                    $bundling->perlu_diproduksi = $stokMaksimal <= 0;
                    return $bundling;
                });

            $daftarPromoEvent = PromoProdukEvent::with('produk')
                ->berlaku()
                ->get();

            $daftarDitahan = PesananDitahan::with(['transaksi.voucher', 'transaksi.stampRedemptions.produk'])->orderBy('created_at', 'desc')->get();
            $daftarMember = \App\Models\Member::orderBy('nama')->get();
            $pengaturanPajak = PengaturanPajak::getAktif();

            return view('pages.transaksi.indeks', [
                'title' => 'Transaksi Kasir',
                'daftarProduk' => $daftarProduk,
                'daftarKategori' => $daftarKategori,
                'daftarBundling' => $daftarBundling,
                'daftarPromoEvent' => $daftarPromoEvent,
                'daftarDitahan' => $daftarDitahan,
                'daftarMember' => $daftarMember,
                'pengaturanPajak' => $pengaturanPajak,
                // Gambar QRIS aktif yang diatur admin (fallback ke placeholder kalau belum ada).
                'qrisUrl' => QrisImage::urlAktif(),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
    private function prosesItemBundlingTransaksi(Transaksi $transaksi, array $item): void
    {
        $bundlingId = (int) $item['bundling_id'];
        $jumlahBundling = (int) $item['jumlah'];
        $bundling = Bundling::with('items.produk', 'items.detailProduk')->findOrFail($bundlingId);

        $perluProduksiBundling = (bool) ($item['butuh_produksi'] ?? false);

        // Bundling bebas: kasir memilih rasa (dari produk + ukuran yang ditentukan admin) lewat modal di POS.
        // Pilihan dikirim di item['pilihan_khusus'] dan dikunci per baris isi + rasa (detail_produk_id), jadi
        // rasa berbeda dari produk yang sama tidak tertukar. Bundling tetap: pakai qty tetap dari paket.
        $isBebas = $bundling->isBebas();
        $pilihanBebas = $isBebas ? ($item['pilihan_khusus'] ?? []) : [];

        if ($isBebas && !$bundling->pilihanBebasLengkap($pilihanBebas)) {
            throw new \RuntimeException("Pilihan isi untuk \"{$bundling->nama_bundling}\" belum lengkap (harus pas {$bundling->targetQtyBebas()} pcs) atau ada rasa yang sudah tidak tersedia.");
        }

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => null,
            'bundling_id' => $bundling->id,
            'nama_produk' => $bundling->nama_bundling,
            'harga_satuan' => $bundling->harga,
            'jumlah' => $jumlahBundling,
            'catatan' => $item['catatan'] ?? null,
            'subtotal' => $bundling->harga * $jumlahBundling,
            'butuh_produksi' => $perluProduksiBundling,
        ]);

        foreach ($bundling->barisIsi($pilihanBebas) as $baris) {
            $bundlingItem = $baris['item'];
            $produk = $bundlingItem->produk;
            if (!$produk) {
                continue;
            }

            $qtySatuBundling = (int) $baris['qty'];
            if ($qtySatuBundling <= 0) {
                continue;
            }

            $catatanProduk = $baris['catatan'];
            $totalDibutuhkan = (int) ($qtySatuBundling * $jumlahBundling);

            // ── Sistem baru: deduct stok dari DetailProduk ──
            if ($bundlingItem->detail_produk_id) {
                $varian = DetailProduk::where('id', $bundlingItem->detail_produk_id)
                    ->lockForUpdate()
                    ->first();

                if (!$varian) {
                    continue;
                }

                $stokTersedia = (int) $varian->stok;
                $stokReady = min($stokTersedia, $totalDibutuhkan);
                $stokProduksi = $totalDibutuhkan - $stokReady;

                // Tentukan apakah item ini perlu diproduksi berdasarkan stok
                $perluProduksi = $varian->perlu_diproduksi || $stokTersedia <= 0;

                if ($perluProduksi) {
                    // Semua masuk produksi, stok tidak dikurangi
                    $stokReady = 0;
                    $stokProduksi = $totalDibutuhkan;
                }

                $ukuranCatat = $bundlingItem->ukuran ?: $varian->ukuran;
                $variasiCatat = $bundlingItem->varian_rasa ?: $varian->variasi;
                $kodeCatat = $varian->kode_barcode;

                if ($bundlingItem->varian_produk_id) {
                    $vp = \App\Models\VarianProduk::find($bundlingItem->varian_produk_id);
                    if ($vp) {
                        $ukuranCatat = $vp->ukuran ?: $ukuranCatat;
                        $variasiCatat = $vp->variasi ?: $variasiCatat;
                        $kodeCatat = $vp->kode_produk ?: $kodeCatat;
                    }
                }

                // Bagian ready: kurangi stok varian
                if ($stokReady > 0) {
                    $varian->stok = $stokTersedia - $stokReady;
                    $varian->status_ketersediaan = $varian->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
                    $varian->save();

                    $this->sinkronStokProduk($produk);

                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $produk->id,
                        'detail_produk_id' => $varian->id,
                        'bundling_id' => $bundling->id,
                        'nama_produk' => $produk->nama_produk,
                        'ukuran' => $ukuranCatat,
                        'varian_rasa' => $variasiCatat,
                        'kode_barcode' => $kodeCatat,
                        'harga_satuan' => 0,
                        'jumlah' => $stokReady,
                        'catatan' => $catatanProduk,
                        'subtotal' => 0,
                        'butuh_produksi' => false,
                    ]);
                }

                // Bagian produksi
                if ($stokProduksi > 0) {
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $produk->id,
                        'detail_produk_id' => $varian->id,
                        'bundling_id' => $bundling->id,
                        'nama_produk' => $produk->nama_produk,
                        'ukuran' => $ukuranCatat,
                        'varian_rasa' => $variasiCatat,
                        'kode_barcode' => $kodeCatat,
                        'harga_satuan' => 0,
                        'jumlah' => $stokProduksi,
                        'catatan' => $catatanProduk,
                        'subtotal' => 0,
                        'butuh_produksi' => true,
                    ]);
                }

                continue;
            }

            // ── Fallback: sistem lama (ukuran + varian_rasa string) ──
            $ukuranItem = $bundlingItem->ukuran;
            $rasaItem = $produk->punya_varian_rasa ? ($bundlingItem->varian_rasa ?: null) : null;

            $terpakai = [];
            $alokasi = $produk->alokasiStok($ukuranItem, $rasaItem ? [$rasaItem] : [], $totalDibutuhkan, $terpakai);

            // Bagian yang stoknya ready: kurangi stok (ukuran + rasa) & catat rasanya.
            foreach ($alokasi['ready'] as $bagian) {
                $produk->kurangiStokKombinasi($ukuranItem, $bagian['varian_rasa'], (int) $bagian['jumlah']);

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'bundling_id' => $bundling->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $ukuranItem,
                    'varian_rasa' => $bagian['varian_rasa'],
                    'harga_satuan' => 0,
                    'jumlah' => $bagian['jumlah'],
                    'catatan' => $catatanProduk,
                    'subtotal' => 0,
                    'butuh_produksi' => false,
                ]);
            }

            // Sisanya perlu diproduksi → baris ini yang masuk ke tiket produksi.
            if ($alokasi['produksi'] > 0) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'bundling_id' => $bundling->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $ukuranItem,
                    'varian_rasa' => $rasaItem,
                    'harga_satuan' => 0,
                    'jumlah' => $alokasi['produksi'],
                    'catatan' => $catatanProduk,
                    'subtotal' => 0,
                    'butuh_produksi' => true,
                ]);
            }
        }
    }

    private function prosesItemPromoEventTransaksi(Transaksi $transaksi, array $item): void
    {
        $promoEventId = (int) $item['promo_event_id'];
        $jumlahEvent = (int) $item['jumlah'];
        $promoEvent = PromoProdukEvent::with('produk.detailProduks')->findOrFail($promoEventId);
        $produk = $promoEvent->produk;

        $perluProduksiEvent = (bool) ($item['butuh_produksi'] ?? false);

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'produk_id' => null,
            'promo_event_id' => $promoEvent->id,
            'nama_produk' => $promoEvent->nama_event,
            'harga_satuan' => $promoEvent->harga_event,
            'jumlah' => $jumlahEvent,
            'catatan' => $item['catatan'] ?? null,
            'subtotal' => $promoEvent->harga_event * $jumlahEvent,
            'butuh_produksi' => $perluProduksiEvent,
        ]);

        if (!$produk) {
            return;
        }

        $totalDibutuhkan = (int) ($promoEvent->isi_event * $jumlahEvent);

        // ── Sistem baru: cari DetailProduk yang cocok dengan pembatasan event ──
        $ukuranEvent = $promoEvent->ukuranTunggalUntukProduksi();
        $kandidatRasa = $promoEvent->kandidatRasaUntukProduksi();

        // Cari semua DetailProduk yang cocok dengan pembatasan ukuran & rasa event
        $variansCocok = $produk->detailProduks
            ->filter(function (DetailProduk $v) use ($ukuranEvent, $kandidatRasa) {
                if (!$v->status_aktif) return false;
                // Filter ukuran
                if ($ukuranEvent && $v->ukuran && strcasecmp($v->ukuran, $ukuranEvent) !== 0) return false;
                if ($ukuranEvent && !$v->ukuran) return false;
                // Filter rasa/variasi
                if (!empty($kandidatRasa)) {
                    $cocok = false;
                    foreach ($kandidatRasa as $rasa) {
                        if ($v->variasi && strcasecmp($v->variasi, $rasa) === 0) {
                            $cocok = true;
                            break;
                        }
                    }
                    if (!$cocok && $v->variasi) return false;
                }
                return true;
            })
            ->values();

        // Jika ada DetailProduk yang cocok, gunakan sistem baru
        if ($variansCocok->isNotEmpty()) {
            $sisa = $totalDibutuhkan;

            foreach ($variansCocok as $varian) {
                if ($sisa <= 0) break;

                $varian = DetailProduk::where('id', $varian->id)->lockForUpdate()->first();
                if (!$varian) continue;

                $stokTersedia = (int) $varian->stok;
                $perluProduksi = $varian->perlu_diproduksi || $stokTersedia <= 0;

                if (!$perluProduksi && $stokTersedia > 0) {
                    $ambil = min($stokTersedia, $sisa);

                    $varian->stok = $stokTersedia - $ambil;
                    $varian->status_ketersediaan = $varian->stok > 0 ? 'stok_ada' : 'perlu_diproduksi';
                    $varian->save();

                    $this->sinkronStokProduk($produk);

                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $produk->id,
                        'detail_produk_id' => $varian->id,
                        'promo_event_id' => $promoEvent->id,
                        'nama_produk' => $produk->nama_produk,
                        'ukuran' => $varian->ukuran,
                        'varian_rasa' => $varian->variasi,
                        'harga_satuan' => 0,
                        'jumlah' => $ambil,
                        'catatan' => null,
                        'subtotal' => 0,
                        'butuh_produksi' => false,
                    ]);

                    $sisa -= $ambil;
                }
            }

            // Sisa yang tidak terpenuhi stoknya → masuk produksi
            if ($sisa > 0) {
                // Pakai varian pertama yang cocok untuk label di tiket produksi
                $varianProduksi = $variansCocok->first();

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'detail_produk_id' => $varianProduksi->id,
                    'promo_event_id' => $promoEvent->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $varianProduksi->ukuran,
                    'varian_rasa' => $varianProduksi->variasi,
                    'harga_satuan' => 0,
                    'jumlah' => $sisa,
                    'catatan' => null,
                    'subtotal' => 0,
                    'butuh_produksi' => true,
                ]);
            }

            return;
        }

        // ── Fallback: sistem lama (ukuran + varian_rasa string) ──
        $rasaTunggal = count($kandidatRasa) === 1 ? $kandidatRasa[0] : null;

        $terpakai = [];
        $alokasi = $produk->alokasiStok($ukuranEvent, $kandidatRasa, $totalDibutuhkan, $terpakai);

        foreach ($alokasi['ready'] as $bagian) {
            $produk->kurangiStokKombinasi($ukuranEvent, $bagian['varian_rasa'], (int) $bagian['jumlah']);

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'promo_event_id' => $promoEvent->id,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $ukuranEvent,
                'varian_rasa' => $bagian['varian_rasa'],
                'harga_satuan' => 0,
                'jumlah' => $bagian['jumlah'],
                'catatan' => null,
                'subtotal' => 0,
                'butuh_produksi' => false,
            ]);
        }

        if ($alokasi['produksi'] > 0) {
            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'promo_event_id' => $promoEvent->id,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $ukuranEvent,
                'varian_rasa' => $rasaTunggal,
                'harga_satuan' => 0,
                'jumlah' => $alokasi['produksi'],
                'catatan' => null,
                'subtotal' => 0,
                'butuh_produksi' => true,
            ]);
        }
    }

    /**
     * Item produk yang dipilih lewat detail produk (ukuran + variasi dari tabel
     * detail_produks): baris detail dikunci, stok ready dipakai dulu dan hanya
     * sebanyak yang ada, sisanya dicatat sebagai "perlu diproduksi" tanpa
     * mengurangi stok. Contoh: beli 7, stok 5 => 2 baris detail transaksi,
     * 5 ready (stok berkurang 5) dan 2 perlu diproduksi.
     * Item yang ditandai butuh_produksi dari kasir seluruhnya perlu diproduksi.
     * Detail produk nonaktif ditolak.
     */
    private function prosesItemVarianTransaksi(Transaksi $transaksi, array $item): void
    {
        $jumlah = (int) ($item['jumlah'] ?? 0);
        if ($jumlah <= 0) {
            throw new \Exception('Jumlah item tidak valid.');
        }

        $detailProduk = DetailProduk::where('id', (int) $item['varian_id'])
            ->where('produk_id', (int) ($item['produk_id'] ?? 0))
            ->lockForUpdate()
            ->first();

        if (!$detailProduk || !$detailProduk->status_aktif) {
            throw new \Exception('Detail produk tidak ditemukan atau sudah dinonaktifkan. Muat ulang halaman kasir.');
        }

        $produk = Produk::findOrFail($detailProduk->produk_id);

        // Produk Stok Bersama: varian (topping dsb.) dipilih kasir, stok & harga tetap dari detail produk.
        $ukuranCatat = $detailProduk->ukuran;
        $variasiCatat = $detailProduk->variasi;
        $kodeCatat = $detailProduk->kode_barcode;
        $namaLengkapCatat = $detailProduk->nama_lengkap;

        if ($produk->jumlahVarianBersama() > 0) {
            $vpId = (int) str_replace('vp_', '', (string) ($item['varian_produk_id'] ?? 0));
            $varianBersama = $produk->varianProduks()
                ->where('id', $vpId)
                ->where('status_aktif', true)
                ->first();

            if (!$varianBersama) {
                throw new \Exception("Varian untuk '{$produk->nama_produk}' belum dipilih atau sudah dinonaktifkan. Muat ulang halaman kasir.");
            }

            $ukuranCatat = $varianBersama->ukuran;
            $variasiCatat = $varianBersama->variasi;
            $kodeCatat = $varianBersama->kode_produk;
            $namaLengkapCatat = $varianBersama->nama_lengkap;
        }

        $label = $produk->nama_produk . ($namaLengkapCatat ? " ({$namaLengkapCatat})" : '');

        if (isset($item['harga_jual']) && abs((float) $item['harga_jual'] - (float) $detailProduk->harga_jual) > 0.5) {
            throw new \Exception("Harga '{$label}' sudah berubah. Muat ulang halaman kasir lalu coba lagi.");
        }

        $jumlahReady = !empty($item['butuh_produksi'])
            ? 0
            : min($jumlah, max(0, (int) $detailProduk->stok));
        $jumlahProduksi = $jumlah - $jumlahReady;

        if ($jumlahReady > 0) {
            // Status ketersediaan ikut dihitung otomatis dari stok oleh model DetailProduk.
            $detailProduk->stok = (int) $detailProduk->stok - $jumlahReady;
            $detailProduk->save();

            $this->sinkronStokProduk($produk);
        }

        foreach ([[$jumlahReady, false], [$jumlahProduksi, true]] as [$qty, $perluProduksi]) {
            if ($qty <= 0) {
                continue;
            }

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id' => $produk->id,
                'detail_produk_id' => $detailProduk->id,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $ukuranCatat,
                'varian_rasa' => $variasiCatat,
                'kode_barcode' => $kodeCatat,
                'harga_satuan' => $detailProduk->harga_jual,
                'jumlah' => $qty,
                'catatan' => $item['catatan'] ?? null,
                'subtotal' => $detailProduk->harga_jual * $qty,
                'butuh_produksi' => $perluProduksi,
            ]);
        }
    }

    /** Stok & status produk dihitung dari detail produk, jadi cukup menyegarkan cache stoknya. */
    private function sinkronStokProduk(Produk $produk): void
    {
        $produk->segarkanStok();
    }

    private function kembalikanStokVarian(DetailTransaksi $detail): void
    {
        $varian = DetailProduk::where('id', $detail->detail_produk_id)->lockForUpdate()->first();
        if (!$varian) {
            Log::warning('Detail produk sudah dihapus, stok tidak dikembalikan', [
                'detail_transaksi_id' => $detail->id,
                'detail_produk_id' => $detail->detail_produk_id,
            ]);
            return;
        }

        $varian->stok = (int) $varian->stok + (int) $detail->jumlah;
        $varian->save();

        $produk = Produk::find($varian->produk_id);
        if ($produk) {
            $this->sinkronStokProduk($produk);
        }
    }

    private function kembalikanStokDariDetail($detailTransaksi): void
    {
        foreach ($detailTransaksi as $detail) {
            if (!$detail->produk_id) {
                continue;
            }

            // Detail dari varian baru: kembalikan ke baris varian yang sama.
            if ($detail->detail_produk_id) {
                if (!$detail->butuh_produksi) {
                    $this->kembalikanStokVarian($detail);
                }
                continue;
            }

            if (!$detail->butuh_produksi) {
                try {
                    $produk = Produk::with(['detailProduks'])->find($detail->produk_id);
                    if ($produk) {
                        // Kalau detail punya varian_rasa, kembalikan lewat
                        // ubahStokRasa supaya stok di keempat tabel sinkron.
                        if ($detail->varian_rasa && $produk->punya_varian_rasa) {
                            $produk->ubahStokRasa($detail->ukuran, $detail->varian_rasa, $detail->jumlah);
                        } else {
                            $produk->incrementStokUkuran($detail->ukuran, $detail->jumlah);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('Gagal mengembalikan stok dari detail transaksi', [
                        'detail_transaksi_id' => $detail->id ?? null,
                        'produk_id' => $detail->produk_id,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        }
    }

    public function simpanDitahan(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nama_pembeli' => 'nullable|string|max:255',
                'member_id' => 'nullable|exists:members,id',
                'voucher_id' => 'nullable|integer|exists:reward_katalog,id',
                'stamp_redemption_id' => 'nullable|integer|exists:stamp_redemptions,id',
                'item_pesanan' => 'required|array',
                'subtotal' => 'required|numeric',
                'pajak' => 'required|numeric',
                'diskon' => 'required|numeric',
                'diskon_persen' => 'nullable|numeric',
                'diskon_label' => 'nullable|string|max:255',
                'total' => 'required|numeric',
                'catatan' => 'nullable|string',
            ]);

            $pengaturanPajak = PengaturanPajak::getAktif();
            if (!$pengaturanPajak->status_aktif) {
                $validated['pajak'] = 0;
            }

            $namaPembeli = $validated['nama_pembeli'] ?? 'Pelanggan Umum';

            $voucher = $this->validasiVoucherUntukTransaksi(
                $validated['voucher_id'] ?? null,
                $validated['member_id'] ?? null
            );
            $rewardStampSiap = $this->validasiRewardStampUntukTransaksi(
                $validated['stamp_redemption_id'] ?? null,
                $validated['member_id'] ?? null
            );

            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_pembeli' => $namaPembeli,
                'member_id' => $validated['member_id'] ?? null,
                'voucher_id' => $voucher?->id,
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'diskon_persen' => $validated['diskon_persen'] ?? 0,
                'diskon_label' => $request->diskon_label,
                'total_bayar' => $validated['total'],
                'jumlah_diterima' => 0,
                'kembalian' => 0,
                'metode_pembayaran' => 'Belum Bayar',
                'status_pembayaran' => 'Belum Lunas',
                'kode_pickup' => Transaksi::buatKodePickupUnik(),
            ]);

            $this->prosesRewardStampTransaksi($transaksi, $rewardStampSiap);

            foreach ($validated['item_pesanan'] as $item) {

                if (!empty($item['is_reward_stamp'])) {
                    continue;
                }

                if (!empty($item['is_bundling']) && !empty($item['bundling_id'])) {
                    $this->prosesItemBundlingTransaksi($transaksi, $item);
                    continue;
                }

                if (!empty($item['is_promo_event']) && !empty($item['promo_event_id'])) {
                    $this->prosesItemPromoEventTransaksi($transaksi, $item);
                    continue;
                }

                if (!empty($item['varian_id'])) {
                    $this->prosesItemVarianTransaksi($transaksi, $item);
                    continue;
                }

                $produkCek = Produk::with(['detailProduks', 'varianProduks'])->find($item['produk_id'] ?? 0);
                if ($produkCek) {
                    if (!$produkCek->punya_varian) {
                        $detailUtama = $produkCek->daftarDetail()->first();
                        if ($detailUtama) {
                            $item['varian_id'] = $detailUtama->id;
                            $this->prosesItemVarianTransaksi($transaksi, $item);
                            continue;
                        }
                    } else {
                        throw new \Exception("Pilih ukuran/variasi untuk produk '{$produkCek->nama_produk}' terlebih dahulu.");
                    }
                }

                 $produk = Produk::with(['detailProduks'])->findOrFail($item['produk_id']);
                $perluProduksi = $this->tentukanPerluProduksi($produk, $item);
                $ukuranItem = $item['ukuran'] ?? null;
                $varianRasa = $item['varian_rasa'] ?? null;

                if (!$perluProduksi) {
                    $stokTersediaUntukUkuran = $produk->stokUntukUkuranRasa($ukuranItem, $varianRasa);
                    if ($stokTersediaUntukUkuran < $item['jumlah']) {
                        $label = $produk->nama_produk;
                        if ($varianRasa) $label .= " ({$varianRasa})";
                        if ($ukuranItem) $label .= " ukuran {$ukuranItem}";
                        throw new \Exception("Stok untuk produk '{$label}' tidak mencukupi (Tersedia: {$stokTersediaUntukUkuran}, Diminta: {$item['jumlah']})");
                    }

                    try {
                        // Kurangi stok per kombinasi (rasa + ukuran) kalau ada,
                        // fallback ke stok ukuran saja.
                        if ($varianRasa && $produk->punya_varian_rasa) {
                            $produk->ubahStokRasa($ukuranItem, $varianRasa, -$item['jumlah']);
                        } else {
                            $produk->decrementStokUkuran($ukuranItem, $item['jumlah']);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengurangi stok saat menahan pesanan', [
                            'produk_id' => $produk->id,
                            'ukuran' => $ukuranItem,
                            'varian_rasa' => $varianRasa,
                            'jumlah' => $item['jumlah'],
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                }

                $detail = DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $ukuranItem,
                    'varian_rasa' => $item['varian_rasa'] ?? null,
                    'harga_satuan' => $item['harga_jual'],
                    'jumlah' => $item['jumlah'],
                    'catatan' => $item['catatan'] ?? null,
                    'subtotal' => $item['harga_jual'] * $item['jumlah'],
                ]);

                $detail->butuh_produksi = $perluProduksi;
                $detail->save();
            }

             $pesanan = PesananDitahan::create([
                'nama_pembeli' => $namaPembeli,
                'item_pesanan' => $validated['item_pesanan'],
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'diskon_persen' => $validated['diskon_persen'] ?? 0,
                'diskon_label' => $request->diskon_label,
                'total' => $validated['total'],
                'catatan' => $validated['catatan'],
                'transaksi_id' => $transaksi->id,
            ]);

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pesanan)
                ->withProperties([
                    'pesanan_id' => $pesanan->id,
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $kodeTransaksi,
                    'nama_pembeli' => $pesanan->nama_pembeli,
                ])
                ->log('menahan pesanan (bayar nanti)');

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan berhasil masuk ke daftar Belum Dibayar.',
                'data' => $pesanan->load('transaksi'),
                'kode_transaksi' => $kodeTransaksi,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function ambilDitahan()
    {
        try {
            $daftarDitahan = PesananDitahan::with(['transaksi.voucher', 'transaksi.stampRedemptions.produk'])->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $daftarDitahan
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function pulihkanDitahan($id)
    {
        DB::beginTransaction();
        try {
            $pesanan = PesananDitahan::with(['transaksi.voucher', 'transaksi.stampRedemptions.produk'])->findOrFail($id);
             if ($pesanan->transaksi) {
                $detailTransaksi = DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->get();
                $this->kembalikanStokDariDetail($detailTransaksi);
                 $this->kembalikanRewardStampDariTransaksi($pesanan->transaksi);
                 VoucherUsage::where('transaksi_id', $pesanan->transaksi->id)->delete();

                 DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->delete();
                $pesanan->transaksi->delete();
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pesanan)
                ->withProperties([
                    'pesanan_id' => $pesanan->id,
                    'nama_pembeli' => $pesanan->nama_pembeli,
                ])
                ->log('memulihkan pesanan ditahan');

            $pesanan->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan berhasil dilanjutkan.',
                'data' => $pesanan
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapusDitahan($id)
    {
        DB::beginTransaction();
        try {
            $pesanan = PesananDitahan::with('transaksi')->findOrFail($id);

            if ($pesanan->transaksi) {
                $detailTransaksi = DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->get();
                $this->kembalikanStokDariDetail($detailTransaksi);

                $this->kembalikanRewardStampDariTransaksi($pesanan->transaksi);
                VoucherUsage::where('transaksi_id', $pesanan->transaksi->id)->delete();

                DetailTransaksi::where('transaksi_id', $pesanan->transaksi->id)->delete();
                $pesanan->transaksi->delete();
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pesanan)
                ->withProperties([
                    'pesanan_id' => $pesanan->id,
                    'nama_pembeli' => $pesanan->nama_pembeli,
                ])
                ->log('menghapus pesanan ditahan');

            $pesanan->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan ditahan berhasil dihapus.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function lunaskanDitahan(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pesanan = PesananDitahan::with('transaksi')->findOrFail($id);

            if (!$pesanan->transaksi) {
                throw new \Exception('Transaksi terkait tidak ditemukan.');
            }

            $validated = $request->validate([
                'jumlah_diterima' => 'required|numeric',
                'kembalian' => 'required|numeric',
                'metode_pembayaran' => 'required|string',
                'member_id' => 'nullable|exists:members,id',
                'voucher_id' => 'nullable|integer|exists:reward_katalog,id',
                'diskon_label' => 'nullable|string|max:255',
            ]);

            $transaksi = $pesanan->transaksi;
            $memberIdEfektif = $validated['member_id'] ?? $transaksi->member_id;

               $voucherDipakai = $this->validasiVoucherUntukTransaksi(
                $request->has('voucher_id') ? ($validated['voucher_id'] ?? null) : $transaksi->voucher_id,
                $memberIdEfektif
            );

             $transaksi->update([
                'jumlah_diterima' => $validated['jumlah_diterima'],
                'kembalian' => $validated['kembalian'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'member_id' => $memberIdEfektif,
                'voucher_id' => $voucherDipakai?->id,
                'status_pembayaran' => 'Lunas',
                'diskon_label' => $validated['diskon_label'] ?? $transaksi->diskon_label,
            ]);

               if ($voucherDipakai) {
                $this->tandaiVoucherDigunakan($voucherDipakai, $transaksi);
            }

             if (!empty($memberIdEfektif)) {
                $pengaturan = PengaturanMember::aktifSekarang();

                if ($pengaturan) {
                    $nominalUntukPoin = $transaksi->subtotal + $transaksi->pajak;
                    $kelipatan = floor($nominalUntukPoin / $pengaturan->minimal_belanja);
                    $poinDiperoleh = $kelipatan * $pengaturan->poin;

                    $member = Member::find($memberIdEfektif);
                    if ($member) {
                        $poinSebelum = $member->poin;
                        if ($poinDiperoleh > 0) {
                            $member->increment('poin', (int) $poinDiperoleh);
                        }
                        $transaksi->update([
                            'poin_sebelum' => $poinSebelum,
                            'poin_didapat' => (int) $poinDiperoleh,
                        ]);
                    }
                }
            }

             if (!empty($memberIdEfektif)) {
                $stampProgram = StampProgram::aktifSekarang();

                if ($stampProgram) {
                    // Stamp dihitung dari total akhir (setelah diskon), maksimal 1 per transaksi.
                    $stampDidapat = $stampProgram->hitungStampDidapat($transaksi->total_bayar);

                    if ($stampDidapat > 0) {
                        $memberStamp = MemberStamp::firstOrCreate(
                            ['member_id' => $memberIdEfektif, 'stamp_program_id' => $stampProgram->id],
                            ['jumlah_stamp' => 0]
                        );

                        $stampSebelum = $memberStamp->jumlah_stamp;
                        $stampBaru = min(
                            $stampSebelum + $stampDidapat,
                            $stampProgram->jumlah_stamp_dibutuhkan
                        );

                        $memberStamp->update(['jumlah_stamp' => $stampBaru]);

                        $transaksi->update([
                            'stamp_program_id' => $stampProgram->id,
                            'stamp_sebelum' => $stampSebelum,
                            'stamp_didapat' => $stampBaru - $stampSebelum,
                        ]);
                    }
                }
            }

             $pesanan->delete();

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($transaksi)
                ->withProperties([
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'nama_pembeli' => $transaksi->nama_pembeli,
                ])
                ->log('melunaskan pesanan');

            return response()->json([
                'status' => 'success',
                'pesan' => 'Pesanan berhasil dilunaskan.',
                'kode_transaksi' => $transaksi->kode_transaksi,
                'data' => $transaksi
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function bayar(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nama_pembeli' => 'nullable|string|max:255',
                'member_id' => 'nullable|exists:members,id',
                'voucher_id' => 'nullable|integer|exists:reward_katalog,id',
                'stamp_redemption_id' => 'nullable|integer|exists:stamp_redemptions,id',
                'item_pesanan' => 'required|array',
                'subtotal' => 'required|numeric',
                'pajak' => 'required|numeric',
                'diskon' => 'required|numeric',
                'diskon_persen' => 'nullable|numeric',
                'diskon_label' => 'nullable|string|max:255',
                'total_bayar' => 'required|numeric',
                'jumlah_diterima' => 'required|numeric',
                'kembalian' => 'required|numeric',
                'metode_pembayaran' => 'required|string',
            ]);

            $pengaturanPajak = PengaturanPajak::getAktif();
            if (!$pengaturanPajak->status_aktif) {
                $validated['pajak'] = 0;
            }

             $voucherDipakai = $this->validasiVoucherUntukTransaksi(
                $validated['voucher_id'] ?? null,
                $validated['member_id'] ?? null
            );

               $rewardStampSiap = $this->validasiRewardStampUntukTransaksi(
                $validated['stamp_redemption_id'] ?? null,
                $validated['member_id'] ?? null
            );

              $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'nama_pembeli' => $validated['nama_pembeli'] ?? 'Pelanggan Umum',
                'member_id' => $validated['member_id'] ?? null,
                'voucher_id' => $voucherDipakai?->id,
                'subtotal' => $validated['subtotal'],
                'pajak' => $validated['pajak'],
                'diskon' => $validated['diskon'],
                'diskon_persen' => $validated['diskon_persen'] ?? 0,
                'diskon_label' => $request->diskon_label,
                'total_bayar' => $validated['total_bayar'],
                'jumlah_diterima' => $validated['jumlah_diterima'],
                'kembalian' => $validated['kembalian'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => 'Lunas',
                'kode_pickup' => Transaksi::buatKodePickupUnik(),
            ]);

              if ($voucherDipakai) {
                $this->tandaiVoucherDigunakan($voucherDipakai, $transaksi);
            }

              $this->prosesRewardStampTransaksi($transaksi, $rewardStampSiap);

             if ($transaksi->member_id) {
                $pengaturan = PengaturanMember::aktifSekarang();

                if ($pengaturan) {
                    $nominalUntukPoin = $transaksi->subtotal + $transaksi->pajak;
                    $kelipatan = floor(
                        $nominalUntukPoin / $pengaturan->minimal_belanja
                    );
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
            }

             if ($transaksi->member_id) {
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
                         $stampBaru = min(
                            $stampSebelum + $stampDidapat,
                            $stampProgram->jumlah_stamp_dibutuhkan
                        );

                        $memberStamp->update(['jumlah_stamp' => $stampBaru]);

                        $transaksi->update([
                            'stamp_program_id' => $stampProgram->id,
                            'stamp_sebelum' => $stampSebelum,
                            'stamp_didapat' => $stampBaru - $stampSebelum,
                        ]);
                    }
                }
            }

            foreach ($validated['item_pesanan'] as $item) {

                 if (!empty($item['is_reward_stamp'])) {
                    continue;
                }

                 if (!empty($item['is_bundling']) && !empty($item['bundling_id'])) {
                    $this->prosesItemBundlingTransaksi($transaksi, $item);
                    continue;
                }

                if (!empty($item['is_promo_event']) && !empty($item['promo_event_id'])) {
                    $this->prosesItemPromoEventTransaksi($transaksi, $item);
                    continue;
                }

                if (!empty($item['varian_id'])) {
                    $this->prosesItemVarianTransaksi($transaksi, $item);
                    continue;
                }

                $produkCek = Produk::with(['detailProduks', 'varianProduks'])->find($item['produk_id'] ?? 0);
                if ($produkCek) {
                    if (!$produkCek->punya_varian) {
                        $detailUtama = $produkCek->daftarDetail()->first();
                        if ($detailUtama) {
                            $item['varian_id'] = $detailUtama->id;
                            $this->prosesItemVarianTransaksi($transaksi, $item);
                            continue;
                        }
                    } else {
                        throw new \Exception("Pilih ukuran/variasi untuk produk '{$produkCek->nama_produk}' terlebih dahulu.");
                    }
                }

                 $produk = Produk::with(['detailProduks'])->findOrFail($item['produk_id']);
                $perluProduksi = $this->tentukanPerluProduksi($produk, $item);
                $ukuranItem = $item['ukuran'] ?? null;
                $varianRasa = $item['varian_rasa'] ?? null;

                if (!$perluProduksi) {
                    $stokTersediaUntukUkuran = $produk->stokUntukUkuranRasa($ukuranItem, $varianRasa);
                    if ($stokTersediaUntukUkuran < $item['jumlah']) {
                        $label = $produk->nama_produk;
                        if ($varianRasa) $label .= " ({$varianRasa})";
                        if ($ukuranItem) $label .= " ukuran {$ukuranItem}";
                        throw new \Exception("Stok untuk produk '{$label}' tidak mencukupi (Tersedia: {$stokTersediaUntukUkuran}, Diminta: {$item['jumlah']})");
                    }

                    try {
                        // Kurangi stok per kombinasi (rasa + ukuran) kalau ada,
                        // fallback ke stok ukuran saja.
                        if ($varianRasa && $produk->punya_varian_rasa) {
                            $produk->ubahStokRasa($ukuranItem, $varianRasa, -$item['jumlah']);
                        } else {
                            $produk->decrementStokUkuran($ukuranItem, $item['jumlah']);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengurangi stok saat transaksi kasir', [
                            'produk_id' => $produk->id,
                            'ukuran' => $ukuranItem,
                            'varian_rasa' => $varianRasa,
                            'jumlah' => $item['jumlah'],
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                }

                $detail = DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'ukuran' => $ukuranItem,
                    'varian_rasa' => $item['varian_rasa'] ?? null,
                    'harga_satuan' => $item['harga_jual'],
                    'jumlah' => $item['jumlah'],
                    'catatan' => $item['catatan'] ?? null,
                    'subtotal' => $item['harga_jual'] * $item['jumlah'],
                ]);

                $detail->butuh_produksi = $perluProduksi;
                $detail->save();
            }

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($transaksi)
                ->withProperties([
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $kodeTransaksi,
                    'nama_pembeli' => $transaksi->nama_pembeli,
                ])
                ->log('menyelesaikan transaksi');

            return response()->json([
                'status' => 'success',
                'pesan' => 'Transaksi berhasil diselesaikan.',
                'kode_transaksi' => $kodeTransaksi,
                'data' => $transaksi
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ActivityLogHelper::handleException($e);
        }
    }

    public function promoStatus(Request $request)
    {
        try {
            $produkIds = collect(explode(',', (string) $request->query('produk_ids', '')))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->values();

            $produk = [];

            if ($produkIds->isNotEmpty()) {
                $produk = Produk::with(['promos', 'detailProduks', 'varianProduks'])
                    ->whereIn('id', $produkIds)
                    ->where('status_aktif', true)
                    ->get()
                    ->map(function ($p) {
                        $varian = collect($p->daftarVarianKasir())->where('aktif', true);
                        if ($varian->isNotEmpty()) {
                            $termurah = $varian->sortBy('harga_asli')->first();

                            return [
                                'id' => $p->id,
                                'harga_asli' => (float) $termurah['harga_asli'],
                                'harga_final' => (float) $termurah['harga_diskon'],
                                'ada_promo' => $termurah['harga_diskon'] < $termurah['harga_asli'],
                                'habis' => $varian->where('stok', '>', 0)->isEmpty(),
                            ];
                        }

                        if ($p->punya_ukuran) {
                            $namaTermurah = $p->ukuranTermurah()['nama'] ?? null;
                            $hargaAsli = $p->getHarga($namaTermurah);
                            $hargaFinal = $p->hargaSetelahPromo($namaTermurah);
                        } else {
                            $hargaAsli = (float) $p->harga_jual;
                            $hargaFinal = (float) $p->harga_diskon;
                        }

                        return [
                            'id' => $p->id,
                            'harga_asli' => $hargaAsli,
                            'harga_final' => $hargaFinal,
                            'ada_promo' => $hargaFinal < $hargaAsli,
                            'habis' => $p->habisSemuaUkuran(),
                        ];
                    })
                    ->keyBy('id');
            }

            $promoEvent = PromoProdukEvent::with('produk')
                ->berlaku()
                ->get()
                ->map(function ($pe) {
                    return [
                        'id' => $pe->id,
                        'nama_event' => $pe->nama_event,
                        'harga_event' => (float) $pe->harga_event,
                        'isi_event' => $pe->isi_event,
                        'isi_normal' => $pe->isi_normal,
                        'perlu_diproduksi' => $pe->perlu_diproduksi,
                        'stok_tersedia' => $pe->stok_tersedia,
                        'produk_nama' => $pe->produk->nama_produk ?? '',
                        'produk_foto' => ($pe->produk && $pe->produk->foto) ? asset('storage/' . $pe->produk->foto) : null,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'produk' => $produk,
                    'promo_event' => $promoEvent,
                ],
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function cetak(Request $request, $kodeTransaksi)
    {
        try {
            $transaksi = Transaksi::with(['detailTransaksi.produk', 'member'])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();
            // pelanggan | dapur (tiket produksi, di-hide) | etalase (= Tiket Dapur, semua produk tanpa harga).
            // Nilai di luar daftar itu otomatis jadi struk pelanggan.
            $jenis = \App\Support\StrukHelper::normalisasiJenis($request->get('jenis'));

            // Cash drawer cuma boleh kebuka kalau SEMUA terpenuhi:
            // struk pelanggan + transaksi sudah Lunas + dibayar Tunai, dan
            // cetaknya datang dari alur kasir (?laci=1), bukan cetak ulang
            // dari riwayat/pesanan pickup.
            $bukaLaci = $jenis === 'pelanggan'
                && $request->boolean('laci')
                && strtolower(trim((string) $transaksi->status_pembayaran)) === 'lunas'
                && strtolower(trim((string) $transaksi->metode_pembayaran)) === 'tunai';

            return view('pages.transaksi.cetak', [
                'transaksi' => $transaksi,
                'jenis' => $jenis,
                'bukaLaci' => $bukaLaci,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}