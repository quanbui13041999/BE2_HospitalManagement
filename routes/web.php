<?php
// routes/web.php — thêm vào file routes hiện có

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
// ── AUTH ──────────────────────────────────────────────
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register']);

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
