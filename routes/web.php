<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
Route::get('/', function () {
    return view('welcome'); 
})->name('home');
// Hiển thị form
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

// Xử lý logic
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Form đặt lịch khám
Route::get('/booking', [AppointmentController::class, 'create'])->name('booking.create');

// Xử lý gửi form
Route::post('/booking', [AppointmentController::class, 'store'])->name('booking.store');
// Danh sách lịch đã đặt
Route::get('/my-bookings', [AppointmentController::class, 'index'])->name('booking.index');

// Dời lịch
Route::get('/booking/edit/{id}', [AppointmentController::class, 'edit'])->name('booking.edit');
Route::post('/booking/update/{id}', [AppointmentController::class, 'update'])->name('booking.update');

// Hủy lịch
Route::post('/booking/cancel/{id}', [AppointmentController::class, 'cancel'])->name('booking.cancel');