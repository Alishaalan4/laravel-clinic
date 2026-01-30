<?php

use Illuminate\Support\Facades\Route;
use app\http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\AdminAuthController as AuthAdminAuthController;
use app\http\Controllers\Auth\UserAuthController;
use app\http\Controllers\Auth\DoctorAuthController;


Route::prefix('auth')->group(function () {
    // patient
    Route::post('/user/register',[UserAuthController::class,'register']);
    Route::post('/user/login',[UserAuthController::class,'login']);

    // doctor
    Route::post('/doctor/register',[DoctorAuthController::class,'register']);
    Route::post('/doctor/login',[DoctorAuthController::class,'login']);

    // admin
    Route::post('/admin/login',[AuthAdminAuthController::class,'login']);
});