<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;

// Trang chủ
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ======================
// AUTH ROUTES
// ======================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ======================
// BOOKING (Đặt lịch khám)
// ======================
Route::get('/booking', [AppointmentController::class, 'create'])
    ->name('booking.create');

Route::post('/booking', [AppointmentController::class, 'store'])
    ->name('booking.store');

// Danh sách lịch đã đặt của tôi
Route::get('/my-bookings', [AppointmentController::class, 'index'])
    ->name('booking.index');

// Dời lịch (Edit)
Route::get('/booking/edit/{id}', [AppointmentController::class, 'edit'])
    ->name('booking.edit');

Route::put('/booking/update/{id}', [AppointmentController::class, 'update'])
    ->name('booking.update');
Route::post('/booking/update/{id}', [AppointmentController::class, 'update'])
    ->name('booking.update');
      

// Hủy lịch
Route::post('/booking/cancel/{id}', [AppointmentController::class, 'cancel'])
    ->name('booking.cancel');