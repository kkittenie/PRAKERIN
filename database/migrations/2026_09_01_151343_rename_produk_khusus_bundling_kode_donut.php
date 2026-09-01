<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $map = [
        'SSH-DN001' => 'SSH-DT001',
        'SSH-DN002' => 'SSH-DT002',
        'SSH-DN003' => 'SSH-DT003',
        'SSH-DN004' => 'SSH-DT004',
    ];

    public function up(): void
    {
        foreach ($this->map as $kodeLama => $kodeBaru) {
            DB::table('produks')
                ->where('kode_produk', $kodeLama)
                ->where('is_khusus_bundling', true)
                ->update(['kode_produk' => $kodeBaru]);
        }
    }

    public function down(): void
    {
        foreach ($this->map as $kodeLama => $kodeBaru) {
            DB::table('produks')
                ->where('kode_produk', $kodeBaru)
                ->where('is_khusus_bundling', true)
                ->update(['kode_produk' => $kodeLama]);
        }
    }
};