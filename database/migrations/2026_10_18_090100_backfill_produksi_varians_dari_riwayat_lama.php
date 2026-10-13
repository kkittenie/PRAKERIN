<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pindahkan riwayat produksi lama (produksi_rasas, produksi_ukurans, kolom besar/kecil,
        // atau produksi tanpa rincian) ke produksi_varians, lengkap dengan salinan nama, kode,
        // dan harga. Setelah ini tabel produksi_ukurans dan produksi_rasas tidak dipakai lagi.
        $now = now();

        $adaRasa = Schema::hasTable('produksi_rasas');
        $adaUkuran = Schema::hasTable('produksi_ukurans');

        $sudah = DB::table('produksi_varians')->distinct()->pluck('produksi_id')->flip();
        $kodeProduk = DB::table('produks')->pluck('kode_produk', 'id');
        $hargaProduk = DB::table('produks')->pluck('harga_jual', 'id');
        $details = DB::table('detail_produks')->get()->groupBy('produk_id');

        $cariDetail = function ($produkId, $ukuran, $variasi) use ($details) {
            return ($details[$produkId] ?? collect())->first(
                fn ($d) => strcasecmp((string) $d->ukuran, (string) $ukuran) === 0
                    && strcasecmp((string) $d->variasi, (string) $variasi) === 0
            );
        };

        // produk_ukuran_id => [nama_ukuran, kode_barcode, harga_jual]
        $infoUkuran = [];
        if (Schema::hasTable('produk_ukurans') && Schema::hasTable('ukurans')) {
            foreach (DB::table('produk_ukurans')
                ->join('ukurans', 'ukurans.id', '=', 'produk_ukurans.ukuran_id')
                ->get(['produk_ukurans.id', 'ukurans.nama_ukuran', 'produk_ukurans.kode_barcode', 'produk_ukurans.harga_jual']) as $u) {
                $infoUkuran[$u->id] = $u;
            }
        }

        foreach (DB::table('produksis')->orderBy('id')->get() as $p) {
            if ($sudah->has($p->id)) {
                continue;
            }

            $baris = [];

            $rasa = $adaRasa ? DB::table('produksi_rasas')->where('produksi_id', $p->id)->orderBy('id')->get() : collect();
            $ukuran = $adaUkuran ? DB::table('produksi_ukurans')->where('produksi_id', $p->id)->orderBy('id')->get() : collect();

            if ($rasa->isNotEmpty()) {
                foreach ($rasa as $r) {
                    $u = $r->produk_ukuran_id ? ($infoUkuran[$r->produk_ukuran_id] ?? null) : null;
                    $baris[] = [
                        'ukuran' => $u->nama_ukuran ?? null,
                        'variasi' => $r->nama_rasa,
                        'kode' => $u->kode_barcode ?? null,
                        'harga' => $u->harga_jual ?? null,
                        'hasil' => (int) $r->jumlah_hasil,
                        'kebutuhan' => (int) $r->kebutuhan,
                    ];
                }
            } elseif ($ukuran->isNotEmpty()) {
                foreach ($ukuran as $r) {
                    $u = $infoUkuran[$r->produk_ukuran_id] ?? null;
                    $baris[] = [
                        'ukuran' => $u->nama_ukuran ?? null,
                        'variasi' => null,
                        'kode' => $u->kode_barcode ?? null,
                        'harga' => $u->harga_jual ?? null,
                        'hasil' => (int) $r->jumlah_hasil,
                        'kebutuhan' => (int) $r->kebutuhan,
                    ];
                }
            } elseif ((int) $p->jumlah_besar > 0 || (int) $p->jumlah_kecil > 0) {
                if ((int) $p->jumlah_besar > 0 || (int) $p->kebutuhan_besar > 0) {
                    $baris[] = ['ukuran' => 'Besar', 'variasi' => null, 'kode' => null, 'harga' => null,
                        'hasil' => (int) $p->jumlah_besar, 'kebutuhan' => (int) $p->kebutuhan_besar];
                }
                if ((int) $p->jumlah_kecil > 0 || (int) $p->kebutuhan_kecil > 0) {
                    $baris[] = ['ukuran' => 'Kecil', 'variasi' => null, 'kode' => null, 'harga' => null,
                        'hasil' => (int) $p->jumlah_kecil, 'kebutuhan' => (int) $p->kebutuhan_kecil];
                }
            } else {
                $baris[] = ['ukuran' => null, 'variasi' => null, 'kode' => null, 'harga' => null,
                    'hasil' => (int) $p->jumlah_hasil, 'kebutuhan' => (int) $p->kebutuhan_produksi];
            }

            foreach ($baris as $b) {
                $detail = $cariDetail($p->produk_id, $b['ukuran'], $b['variasi']);

                DB::table('produksi_varians')->insert([
                    'produksi_id' => $p->id,
                    'produk_varian_id' => $detail->id ?? null,
                    'ukuran' => $b['ukuran'],
                    'variasi' => $b['variasi'],
                    'kode_barcode' => $b['kode'] ?: ($detail->kode_barcode ?? ($kodeProduk[$p->produk_id] ?? null)),
                    'harga_jual' => $b['harga'] ?? ($detail->harga_jual ?? ($hargaProduk[$p->produk_id] ?? 0)),
                    'jumlah_hasil' => $b['hasil'],
                    'kebutuhan' => $b['kebutuhan'],
                    'created_at' => $p->created_at ?? $now,
                    'updated_at' => $p->updated_at ?? $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tidak dibalik: data lama tetap ada di tabel asalnya sampai tabel itu dibuang.
    }
};