<?php

use App\Http\Controllers\Member\AkunController;
use App\Http\Controllers\Member\AuthController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\RewardController;
use App\Http\Controllers\Member\RiwayatController;
use Illuminate\Support\Facades\Route;

Route::prefix('member')->name('member.')->group(function () {

    // Welcome / Pre-login Onboarding Screen
    Route::get('/welcome', [AuthController::class, 'welcome'])->name('welcome');

    // Accessible by anyone (Guest & Auth Member): Home & Account
    Route::get('/beranda', [DashboardController::class, 'index'])->name('beranda');
    Route::get('/akun', [AkunController::class, 'index'])->name('akun');

    // Logout (Supports GET and POST safely)
    Route::match(['get', 'post'], '/keluar', [AuthController::class, 'logout'])->name('logout');

    // Guest Auth Routes
    Route::middleware('guest:member')->group(function () {
        Route::get('/masuk', [AuthController::class, 'tampilkanLogin'])->name('login');
        Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
        Route::get('/daftar', [AuthController::class, 'tampilkanRegister'])->name('register');
        Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
        Route::post('/kirim-otp', [AuthController::class, 'kirimOtp'])->name('otp.send');
    });

    // Authenticated Member Routes (Vouchers/Reward, History)
    Route::middleware('auth:member')->group(function () {
        Route::get('/reward', [RewardController::class, 'index'])->name('reward.index');
        Route::get('/reward/{reward}', [RewardController::class, 'show'])->name('reward.show');
        Route::post('/reward/{reward}/tukar', [RewardController::class, 'redeem'])->name('reward.tukar');

        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');
    });
});