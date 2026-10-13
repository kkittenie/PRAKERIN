<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom target member, batas penggunaan, tanggal mulai dan selesai pada reward_katalog
        Schema::table('reward_katalog', function (Blueprint $table) {
            $table->enum('target_member', ['semua', 'khusus'])->default('semua')->after('status_aktif');
            $table->unsignedInteger('batas_penggunaan_per_member')->default(1)->after('target_member');
            $table->date('tanggal_mulai')->nullable()->after('batas_penggunaan_per_member');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });

        // Buat kolom poin_dibutuhkan nullable agar voucher tidak mewajibkan poin
        Schema::table('reward_katalog', function (Blueprint $table) {
            $table->unsignedInteger('poin_dibutuhkan')->default(0)->change();
        });

        // 2. Buat tabel relasi voucher-member (voucher_members)
        Schema::create('voucher_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_katalog_id')->constrained('reward_katalog')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['reward_katalog_id', 'member_id'], 'voucher_member_unique');
        });

        // 3. Buat tabel riwayat penggunaan voucher (voucher_usages)
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_katalog_id')->constrained('reward_katalog')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksi')->nullOnDelete();
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();

            $table->index(['reward_katalog_id', 'member_id'], 'voucher_usage_member_idx');
        });

        // 4. Migrasi data lama:
        // Ubah relasi transaksi.voucher_id dari penukaran_poin.id ke reward_katalog.id
        // Pertama, drop foreign key lama ke penukaran_poin
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
        });

        // Update nilai transaksi.voucher_id dari penukaran_poin.reward_katalog_id
        $penukarans = DB::table('penukaran_poin')->get();
        foreach ($penukarans as $p) {
            // Update transaksi yang merujuk ke penukaran_poin ID ini
            DB::table('transaksi')
                ->where('voucher_id', $p->id)
                ->update(['voucher_id' => $p->reward_katalog_id]);

            // Catat voucher berstatus 'selesai' ke tabel voucher_usages
            if ($p->status === 'selesai') {
                DB::table('voucher_usages')->insert([
                    'reward_katalog_id' => $p->reward_katalog_id,
                    'member_id' => $p->member_id,
                    'transaksi_id' => $p->transaksi_id,
                    'used_at' => $p->diambil_pada ?? $p->updated_at ?? now(),
                    'created_at' => $p->created_at ?? now(),
                    'updated_at' => $p->updated_at ?? now(),
                ]);
            }

            // Konversi voucher lama berstatus 'menunggu_diambil' menjadi voucher khusus member
            if ($p->status === 'menunggu_diambil') {
                // Pastikan voucher target_member menjadi 'khusus'
                DB::table('reward_katalog')
                    ->where('id', $p->reward_katalog_id)
                    ->update([
                        'target_member' => 'khusus',
                        'batas_penggunaan_per_member' => 1,
                    ]);

                // Daftarkan member ke voucher_members jika belum ada
                $exists = DB::table('voucher_members')
                    ->where('reward_katalog_id', $p->reward_katalog_id)
                    ->where('member_id', $p->member_id)
                    ->exists();

                if (!$exists) {
                    DB::table('voucher_members')->insert([
                        'reward_katalog_id' => $p->reward_katalog_id,
                        'member_id' => $p->member_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Pasang foreign key baru dari transaksi.voucher_id ke reward_katalog.id
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('voucher_id')->references('id')->on('reward_katalog')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
        });

        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('voucher_members');

        Schema::table('reward_katalog', function (Blueprint $table) {
            $table->dropColumn([
                'target_member',
                'batas_penggunaan_per_member',
                'tanggal_mulai',
                'tanggal_selesai',
            ]);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('voucher_id')->references('id')->on('penukaran_poin')->nullOnDelete();
        });
    }
};
