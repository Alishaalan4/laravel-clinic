<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\DoctorAuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\DoctorController;
use App\Http\Controllers\User\AppointmentController;


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


Route::middleware(['auth:sanctum','role:user'])->prefix('user')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Doctors
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);
    Route::get('/doctors/search', [DoctorController::class, 'search']);
    Route::get('/doctors/{doctor}/availability', [DoctorController::class, 'availability']);

    // Appointments
    Route::post('/appointments', [AppointmentController::class, 'store']); 
    Route::get('/appointments', [AppointmentController::class, 'index']);
});