<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\NonaktifkanPromoKadaluwarsa;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('produksi:update-status')
    ->everyMinute();

// Push notification ke admin buat alert sistem yang baru muncul
// (stok produk/bahan menipis, produk kadaluarsa).
Schedule::command('notifikasi:push-admin')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Batalkan otomatis pesanan pickup member yang tidak upload bukti pembayaran
// dalam batas waktunya (10 menit); stok & voucher ikut dikembalikan.
Schedule::command('pesanan-pickup:batalkan-kedaluwarsa')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command(NonaktfikanPromoKadaluwarsa::class)->dailyAt('00:05');