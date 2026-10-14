<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Target promo (Potongan Harga) dan Promo Produk Event dipindah dari tabel master lama
 * (ukurans, varian_rasas lewat 4 tabel pembatasan) ke tabel yang menunjuk langsung ke
 * detail_produks. Tidak ada baris untuk sebuah promo/event pada sebuah produk = berlaku
 * ke semua detail produk itu.
 *
 * Backfill: pembatasan ukuran x rasa lama diterjemahkan jadi daftar detail produk yang
 * cocok (dicocokkan lewat nama, tanpa membedakan huruf besar/kecil).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_detail_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->cascadeOnDelete();
            $table->foreignId('detail_produk_id')->constrained('detail_produks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promo_id', 'detail_produk_id'], 'pdp_promo_detail_unique');
        });

        Schema::create('promo_produk_event_detail_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_produk_event_id')->constrained('promo_produk_events')->cascadeOnDelete();
            $table->foreignId('detail_produk_id')->constrained('detail_produks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promo_produk_event_id', 'detail_produk_id'], 'ppedp_event_detail_unique');
        });

        $this->pindahkanPromo();
        $this->pindahkanEvent();

        Schema::dropIfExists('promo_produk_ukurans');
        Schema::dropIfExists('promo_produk_rasas');
        Schema::dropIfExists('promo_produk_event_ukurans');
        Schema::dropIfExists('promo_produk_event_varian_rasas');
    }

    public function down(): void
    {
        // Tabel pembatasan lama tidak dibuat ulang (datanya sudah berpindah ke detail produk).
        Schema::dropIfExists('promo_produk_event_detail_produks');
        Schema::dropIfExists('promo_detail_produks');
    }

    private function pindahkanPromo(): void
    {
        $ukuran = $this->namaPerKunci('promo_produk_ukurans', 'ukurans', 'ukuran_id', 'nama_ukuran', ['promo_id', 'produk_id']);
        $rasa = $this->namaPerKunci('promo_produk_rasas', 'varian_rasas', 'varian_rasa_id', 'nama_rasa', ['promo_id', 'produk_id']);

        $now = now();
        foreach (array_unique(array_merge(array_keys($ukuran), array_keys($rasa))) as $kunci) {
            [$promoId, $produkId] = array_map('intval', explode('-', $kunci));

            $ids = $this->detailCocok($produkId, $ukuran[$kunci] ?? [], $rasa[$kunci] ?? []);
            foreach ($ids as $detailId) {
                DB::table('promo_detail_produks')->insertOrIgnore([
                    'promo_id' => $promoId,
                    'detail_produk_id' => $detailId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function pindahkanEvent(): void
    {
        $ukuran = $this->namaPerKunci('promo_produk_event_ukurans', 'ukurans', 'ukuran_id', 'nama_ukuran', ['promo_produk_event_id']);
        $rasa = $this->namaPerKunci('promo_produk_event_varian_rasas', 'varian_rasas', 'varian_rasa_id', 'nama_rasa', ['promo_produk_event_id']);

        $produkPerEvent = DB::table('promo_produk_events')->pluck('produk_id', 'id');

        $now = now();
        foreach (array_unique(array_merge(array_keys($ukuran), array_keys($rasa))) as $kunci) {
            $eventId = (int) $kunci;
            $produkId = (int) ($produkPerEvent[$eventId] ?? 0);
            if ($produkId === 0) {
                continue;
            }

            $ids = $this->detailCocok($produkId, $ukuran[$kunci] ?? [], $rasa[$kunci] ?? []);
            foreach ($ids as $detailId) {
                DB::table('promo_produk_event_detail_produks')->insertOrIgnore([
                    'promo_produk_event_id' => $eventId,
                    'detail_produk_id' => $detailId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /** Baca tabel pembatasan lama jadi [kunci => [nama, ...]]; kunci = kolom-kolom pemilik digabung "-". */
    private function namaPerKunci(string $tabel, string $master, string $kolomId, string $kolomNama, array $kolomKunci): array
    {
        if (!Schema::hasTable($tabel) || !Schema::hasTable($master)) {
            return [];
        }

        $baris = DB::table($tabel . ' as t')
            ->join($master . ' as m', 'm.id', '=', 't.' . $kolomId)
            ->select(array_merge(
                array_map(fn ($k) => 't.' . $k, $kolomKunci),
                ['m.' . $kolomNama . ' as nama']
            ))
            ->get();

        $hasil = [];
        foreach ($baris as $b) {
            $kunci = implode('-', array_map(fn ($k) => $b->{$k}, $kolomKunci));
            $hasil[$kunci][] = $b->nama;
        }

        return $hasil;
    }

    /** ID detail produk dari satu produk yang ukurannya ada di $namaUkuran DAN variasinya ada di $namaRasa (kosong = tidak dibatasi). */
    private function detailCocok(int $produkId, array $namaUkuran, array $namaRasa): array
    {
        $norm = fn ($x) => mb_strtolower(trim((string) $x));
        $u = array_map($norm, $namaUkuran);
        $r = array_map($norm, $namaRasa);

        return DB::table('detail_produks')
            ->where('produk_id', $produkId)
            ->get(['id', 'ukuran', 'variasi'])
            ->filter(fn ($d) => (empty($u) || in_array($norm($d->ukuran), $u, true))
                && (empty($r) || in_array($norm($d->variasi), $r, true)))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
};