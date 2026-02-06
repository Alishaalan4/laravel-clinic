<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\DoctorAuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\DoctorController;
use App\Http\Controllers\User\AppointmentController;

use App\Http\Controllers\Doctor\AvailabilityController;
use App\Http\Controllers\Doctor\AppointmentController as DoctorAppointmentController;
use App\Http\Controllers\Doctor\ProfileController as DoctorProfileController;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;


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

// user,patient
Route::middleware(['auth:sanctum','role:user'])->prefix('user')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/updatePassword',[ProfileController::class,'updatePassword']);
    // Doctors
    Route::get('/doctors/search', [DoctorController::class, 'search']);
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);
    Route::get('/doctors/{doctor}/availability', [DoctorController::class, 'availability']);

    // Appointments
    Route::post('/appointments', [AppointmentController::class, 'store']); 
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appointment/{appointment}',[AppointmentController::class,'show']);
});

// doctor
Route::middleware(['auth:sanctum','role:doctor'])->prefix('doctor')->group(function () {

    // Profile
    Route::get('/profile', [DoctorProfileController::class, 'index']);
    Route::put('/profile', [DoctorProfileController::class, 'update']);

    // Availability
    Route::post('/availability', [AvailabilityController::class, 'store']);
    Route::get('/availability', [AvailabilityController::class, 'index']);

    // Appointments
    Route::get('/appointments', [DoctorAppointmentController::class, 'index']);
    Route::post('/appointments/{appointment}/accept', [DoctorAppointmentController::class, 'accept']);
    Route::post('/appointments/{appointment}/cancel', [DoctorAppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/complete', [DoctorAppointmentController::class, 'complete']);

    // Stats
    Route::get('/stats', [DoctorAppointmentController::class, 'stats']);
});

// admin
Route::middleware(['auth:sanctum','role:admin'])->prefix('admin')->group(function () {

    // Admin management
    Route::post('/admins', [AdminController::class, 'store']);
    Route::get('/admins/{admin}', [AdminController::class,'show']);
    Route::get('/admins', [AdminController::class,'index']);
    Route::post('/admins/{admin}/changePassword', [AdminController::class,'changePassword']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class,'show']);
    Route::post('/users/{user}/changePassword', [UserController::class,'changePassword']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Doctors
    Route::get('/doctors', [AdminDoctorController::class, 'index']);
    Route::post('/doctors', [AdminDoctorController::class, 'store']);
    Route::get('/doctors/{doctor}', [AdminDoctorController::class,'show']);
    Route::delete('/doctors/{doctor}', [AdminDoctorController::class, 'destroy']);

    // Appointments
    Route::get('/appointments', [AdminAppointmentController::class, 'index']);
    Route::post('/appointments', [AdminAppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AdminAppointmentController::class,'show']);

    // Stats
    Route::get('/stats', [AdminController::class, 'stats']);
});