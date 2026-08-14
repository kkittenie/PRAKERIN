<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberOtpController;

Route::prefix('member')->group(function () {

    Route::post('/send-otp', [
        MemberOtpController::class,
        'sendOtp'
    ]);

    Route::post('/verify-otp', [
        MemberOtpController::class,
        'verifyOtp'
    ]);

    Route::post('/logout', [
        MemberOtpController::class,
        'logout'
    ]);

});