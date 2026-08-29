<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (! Schema::hasColumn('members', 'kode_member')) {
                $table->string('kode_member')->nullable()->unique()->after('id');
            }
        });

        $members = DB::table('members')->whereNull('kode_member')->orderBy('id')->get(['id']);

        foreach ($members as $member) {
            DB::table('members')
                ->where('id', $member->id)
                ->update(['kode_member' => 'MBR' . str_pad((string) $member->id, 6, '0', STR_PAD_LEFT)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'kode_member')) {
                $table->dropColumn('kode_member');
            }
        });
    }
};
