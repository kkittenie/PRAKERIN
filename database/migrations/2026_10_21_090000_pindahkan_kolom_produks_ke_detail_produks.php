<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kolom di tabel produks yang sekarang hanya ada di detail_produks. */
    private array $kolomLama = [
        'kode_produk',
        'harga_jual',
        'harga_modal',
        'stok',
        'stok_minimum',
        'stok_besar',
        'stok_kecil',
        'harga_besar',
        'harga_kecil',
        'status_ketersediaan',
        'punya_ukuran',
        'punya_varian_rasa',
    ];

    public function up(): void
    {
        // 1. Kolom kode di detail_produks diberi nama kode_produk (kode milik tiap detail).
        if (Schema::hasColumn('detail_produks', 'kode_barcode') && !Schema::hasColumn('detail_produks', 'kode_produk')) {
            Schema::table('detail_produks', function (Blueprint $table) {
                $table->renameColumn('kode_barcode', 'kode_produk');
            });
        }

        // 2. Produk yang masih belum punya detail (misalnya produk khusus bundling) dibuatkan
        //    satu detail dari kode, harga, dan stok yang selama ini ada di tabel produks.
        $now = now();
        $tanpaDetail = DB::table('produks')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('detail_produks')->whereColumn('detail_produks.produk_id', 'produks.id');
            })
            ->get();

        foreach ($tanpaDetail as $p) {
            $stok = (int) ($p->stok ?? 0);

            DB::table('detail_produks')->insert([
                'produk_id' => $p->id,
                'kode_produk' => $this->kodeUnik((string) (($p->kode_produk ?? null) ?: 'PRD' . str_pad((string) $p->id, 4, '0', STR_PAD_LEFT))),
                'ukuran' => null,
                'variasi' => null,
                'harga_modal' => $p->harga_modal ?? 0,
                'harga_jual' => $p->harga_jual ?? 0,
                'stok' => $stok,
                'stok_minimum' => (int) ($p->stok_minimum ?? 0),
                'foto' => null,
                'status_ketersediaan' => $stok > 0 ? 'stok_ada' : 'perlu_diproduksi',
                'status_aktif' => 1,
                'urutan' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 3. Detail yang kodenya masih kosong diberi kode otomatis.
        $kosong = DB::table('detail_produks')
            ->where(function ($q) {
                $q->whereNull('kode_produk')->orWhere('kode_produk', '');
            })
            ->orderBy('id')
            ->get();

        foreach ($kosong as $d) {
            $nomor = DB::table('detail_produks')->where('produk_id', $d->produk_id)->where('id', '<=', $d->id)->count();
            $kode = $this->kodeUnik('PRD' . str_pad((string) $d->produk_id, 4, '0', STR_PAD_LEFT) . '-' . str_pad((string) $nomor, 2, '0', STR_PAD_LEFT));

            DB::table('detail_produks')->where('id', $d->id)->update(['kode_produk' => $kode]);
        }

        // 4. Kode dobel (kalau ada) diberi akhiran id supaya bisa dikunci unik.
        $dobel = DB::table('detail_produks')->select('kode_produk')->groupBy('kode_produk')->havingRaw('COUNT(*) > 1')->pluck('kode_produk');

        foreach ($dobel as $kode) {
            foreach (DB::table('detail_produks')->where('kode_produk', $kode)->orderBy('id')->get()->slice(1) as $b) {
                DB::table('detail_produks')->where('id', $b->id)->update(['kode_produk' => $this->kodeUnik($kode . '-' . $b->id)]);
            }
        }

        // 5. Kode detail wajib dan unik.
        Schema::table('detail_produks', function (Blueprint $table) {
            $table->string('kode_produk', 60)->nullable(false)->change();
        });

        try {
            Schema::table('detail_produks', function (Blueprint $table) {
                $table->unique('kode_produk', 'detail_produks_kode_produk_unique');
            });
        } catch (\Throwable $e) {
            // Sudah ada index unik untuk kolom ini: tidak perlu dibuat lagi.
        }

        // 6. Tabel produks hanya menyimpan informasi umum.
        $ada = array_values(array_filter($this->kolomLama, fn ($k) => Schema::hasColumn('produks', $k)));

        if (!empty($ada)) {
            Schema::table('produks', function (Blueprint $table) use ($ada) {
                $table->dropColumn($ada);
            });
        }
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->string('kode_produk', 50)->nullable()->after('kategori_id');
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('harga_modal', 15, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->integer('stok_besar')->default(0);
            $table->integer('stok_kecil')->default(0);
            $table->decimal('harga_besar', 15, 2)->nullable();
            $table->decimal('harga_kecil', 15, 2)->nullable();
            $table->string('status_ketersediaan', 30)->default('stok_ada');
            $table->boolean('punya_ukuran')->default(false);
            $table->boolean('punya_varian_rasa')->default(false);
        });

        // Isi ulang dari detail pertama dan total stok supaya data tidak kosong.
        DB::statement('UPDATE produks SET kode_produk = (SELECT d.kode_produk FROM detail_produks d WHERE d.produk_id = produks.id ORDER BY d.urutan, d.id LIMIT 1)');
        DB::statement('UPDATE produks SET harga_jual = COALESCE((SELECT MIN(d.harga_jual) FROM detail_produks d WHERE d.produk_id = produks.id), 0)');
        DB::statement('UPDATE produks SET harga_modal = COALESCE((SELECT MIN(d.harga_modal) FROM detail_produks d WHERE d.produk_id = produks.id), 0)');
        DB::statement('UPDATE produks SET stok = COALESCE((SELECT SUM(d.stok) FROM detail_produks d WHERE d.produk_id = produks.id), 0)');
        DB::statement('UPDATE produks SET stok_minimum = COALESCE((SELECT SUM(d.stok_minimum) FROM detail_produks d WHERE d.produk_id = produks.id), 0)');
        DB::statement("UPDATE produks SET status_ketersediaan = IF(stok > 0, 'stok_ada', 'perlu_diproduksi')");
        DB::statement('UPDATE produks SET punya_ukuran = EXISTS (SELECT 1 FROM detail_produks d WHERE d.produk_id = produks.id AND d.ukuran IS NOT NULL)');
        DB::statement('UPDATE produks SET punya_varian_rasa = EXISTS (SELECT 1 FROM detail_produks d WHERE d.produk_id = produks.id AND d.variasi IS NOT NULL)');
    }

    /** Kode yang belum dipakai detail mana pun. */
    private function kodeUnik(string $kandidat): string
    {
        $kode = $kandidat;
        $i = 2;

        while (DB::table('detail_produks')->where('kode_produk', $kode)->exists()) {
            $kode = $kandidat . '-' . $i++;
        }

        return $kode;
    }
};