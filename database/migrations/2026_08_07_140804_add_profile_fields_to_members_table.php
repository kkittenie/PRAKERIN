<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (! Schema::hasColumn('members', 'email')) {
                $table->string('email')->nullable()->after('no_hp');
            }
            if (! Schema::hasColumn('members', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('email');
            }
            if (! Schema::hasColumn('members', 'gender')) {
                $table->string('gender')->nullable()->after('tanggal_lahir');
            }
            if (! Schema::hasColumn('members', 'foto')) {
                $table->string('foto')->nullable()->after('gender');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            foreach (['email', 'tanggal_lahir', 'gender', 'foto'] as $column) {
                if (Schema::hasColumn('members', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};