<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

class BroadcastHelper
{
    public static function aman(callable $broadcast): void
    {
        try {
            $broadcast();
        } catch (Throwable $e) {
            Log::warning('Gagal broadcast event pesanan (aksi utama tetap berhasil): ' . $e->getMessage());
        }
    }
}