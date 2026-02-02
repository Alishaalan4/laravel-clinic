<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\DoctorAuthController;
use App\Http\Controllers\Auth\AdminAuthController;

Route::prefix('auth')->group(function () {

    // user (patient)
    Route::post('/user/register', [UserAuthController::class, 'register']);
    Route::post('/user/login',    [UserAuthController::class, 'login']);

    // doctor
    Route::post('/doctor/register', [DoctorAuthController::class, 'register']);
    Route::post('/doctor/login',    [DoctorAuthController::class, 'login']);

    // admin
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
    Route::post('/admin/register', [AdminAuthController::class, 'register']);
});
