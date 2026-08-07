<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (! Schema::hasColumn('members', 'password')) {
                $table->string('password')->nullable()->after('no_hp');
            }
            if (! Schema::hasColumn('members', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
            if (! Schema::hasColumn('members', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            foreach (['password', 'remember_token', 'email_verified_at'] as $column) {
                if (Schema::hasColumn('members', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
