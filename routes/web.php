<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;

// Trang chủ
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── AUTH ──────────────────────────────────────────────
Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

// ── APPOINTMENT (yêu cầu đăng nhập) ──────────────────
Route::middleware('auth')->group(function () {

    // Đặt lịch hẹn
    Route::get('/dat-lich',           [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/dat-lich',          [AppointmentController::class, 'store'])->name('appointments.store');

    // AJAX: lấy khung giờ theo bác sĩ + ngày (dùng cho cả trang đặt lịch và modal dời lịch)
    Route::get('/dat-lich/schedules', [AppointmentController::class, 'getSchedules'])->name('appointments.schedules');

    // Danh sách lịch hẹn
    Route::get('/lich-hen',           [AppointmentController::class, 'index'])->name('appointments.index');
    
    // goi y bac si tu dong
    Route::get('/api/appointments/suggest',   [AppointmentController::class, 'suggest'])
     ->name('appointments.suggest');
    
     // Chi tiết lịch hẹn (nếu có)
    // Route::get('/lich-hen/{id}',   [AppointmentController::class, 'show'])->name('appointments.show');

    // Dời lịch hẹn
    Route::get('/lich-hen/{id}/doi',  [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/lich-hen/{id}/doi',  [AppointmentController::class, 'update'])->name('appointments.update');

    // Hủy lịch hẹn
    Route::post('/lich-hen/{id}/huy', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
});