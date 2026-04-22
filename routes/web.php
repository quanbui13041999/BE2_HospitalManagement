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

// ── BOOKING (yêu cầu đăng nhập) ───────────────────────
Route::middleware('auth')->group(function () {

    // Đặt lịch hẹn
    Route::get('/dat-lich',  [AppointmentController::class, 'create'])->name('booking.create');
    Route::post('/dat-lich', [AppointmentController::class, 'store'])->name('booking.store');

    // Danh sách lịch hẹn
    Route::get('/lich-hen',  [AppointmentController::class, 'index'])->name('booking.index');

    // Dời lịch hẹn
    Route::get('/lich-hen/{id}/doi',  [AppointmentController::class, 'edit'])->name('booking.edit');
    Route::put('/lich-hen/{id}/doi',  [AppointmentController::class, 'update'])->name('booking.update');

    // Hủy lịch hẹn
    Route::post('/lich-hen/{id}/huy', [AppointmentController::class, 'cancel'])->name('booking.cancel');
});

// ── API (AJAX — không cần auth middleware riêng nếu dùng Sanctum)
Route::middleware('auth')->get('/api/schedules', [AppointmentController::class, 'getSchedules'])
     ->name('api.schedules');
