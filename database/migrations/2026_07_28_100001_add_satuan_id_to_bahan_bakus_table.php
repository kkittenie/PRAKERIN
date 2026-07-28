<?php

use App\Models\Satuan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {

            $table->foreignId('satuan_id')
                ->nullable()
                ->after('nama_bahan')
                ->constrained('satuans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

        });

        // Memindahkan data lama dari kolom satuan ke satuan_id
        $data = DB::table('bahan_bakus')->get();

        foreach ($data as $item) {

            $satuan = Satuan::firstOrCreate([
                'nama' => trim($item->satuan)
            ]);

            DB::table('bahan_bakus')
                ->where('id', $item->id)
                ->update([
                    'satuan_id' => $satuan->id
                ]);
        }

        Schema::table('bahan_bakus', function (Blueprint $table) {

            $table->dropColumn('satuan');

        });
    }

    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {

            $table->string('satuan')->nullable();

        });

        $data = DB::table('bahan_bakus')->get();

        foreach ($data as $item) {

            $nama = DB::table('satuans')
                ->where('id', $item->satuan_id)
                ->value('nama');

            DB::table('bahan_bakus')
                ->where('id', $item->id)
                ->update([
                    'satuan' => $nama
                ]);
        }

        Schema::table('bahan_bakus', function (Blueprint $table) {

            $table->dropForeign(['satuan_id']);
            $table->dropColumn('satuan_id');

        });
    }
};