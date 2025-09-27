<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/******************* AUTH ROUTES *******************/
Route::controller(AuthController::class)->group(function () {

    Route::post('login', 'login')->middleware(['throttle:login'])->name('login');
    Route::post('register', 'register')->name('register');
    Route::post('forgot-password', 'forgotPassword')->name('password.forgot');
    Route::post('resend-forgot-password', 'resendForgotPassword')->middleware('throttle:resend')->name('password.forgot.resend');
    Route::post('reset-password', 'resetPassword')->name('password.reset');

    // Routes relation to email 
    Route::post('check-email', 'checkEmail')->name('email.check');
    Route::post('send-verify-email', 'checkEmail')->name('email.check');
    Route::get('verify-email', 'verifyEmail')->name('email.verify');
    Route::post('resend-verify-email', 'resendVerifyEmail')->name('email.verify.resend');
    Route::post('verify-complete', 'complete')->name('email.verify.complete');

    // Routes need check token before action
    Route::middleware('jwt.auth')->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::post('refresh', 'refresh')->name('refresh');
        Route::post('change-password', 'changePassword')->name('password.change');
    });
});


/******************* USER ROUTES *******************/
Route::prefix('u')->apiResource('users', UserController::class)
    // ->middlewareFor(
    //     ['index', 'show', 'store', 'update', 'destroy'],
    //     ['jwt.auth', 'throttle:10,1']
    // )
;
