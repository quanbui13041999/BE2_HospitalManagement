<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ServiceController;

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

    // Chi tiết lịch hẹn (nếu có)
    // Route::get('/lich-hen/{id}',   [AppointmentController::class, 'show'])->name('appointments.show');

    // Dời lịch hẹn
    Route::get('/lich-hen/{id}/doi',  [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/lich-hen/{id}/doi',  [AppointmentController::class, 'update'])->name('appointments.update');

    // Hủy lịch hẹn
    Route::post('/lich-hen/{id}/huy', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
});


//  TÍNH NĂNG 1: Quản lý Dịch vụ & Bảng giá
// ============================================================
Route::prefix('services')->name('services.')->group(function () {
    Route::get('/',              [ServiceController::class, 'index'])->name('index');
    Route::get('/create',        [ServiceController::class, 'create'])->name('create');
    Route::post('/',             [ServiceController::class, 'store'])->name('store');
    Route::get('/{service}',     [ServiceController::class, 'show'])->name('show');
    Route::get('/{service}/edit',[ServiceController::class, 'edit'])->name('edit');
    Route::put('/{service}',     [ServiceController::class, 'update'])->name('update');
    Route::patch('/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('toggle-status');

    // Bảng giá (nested dưới service)
    Route::post('/{service}/prices',                   [ServiceController::class, 'storePrice'])->name('prices.store');
    Route::put('/{service}/prices/{price}',            [ServiceController::class, 'updatePrice'])->name('prices.update');
    Route::delete('/{service}/prices/{price}',         [ServiceController::class, 'destroyPrice'])->name('prices.destroy');
});

// ============================================================
//  TÍNH NĂNG 2: Quản lý Phòng khám & Phân bổ ca
// ============================================================
Route::prefix('rooms')->name('rooms.')->group(function () {
    // CRUD phòng
    Route::get('/',               [RoomController::class, 'index'])->name('index');
    Route::get('/create',         [RoomController::class, 'create'])->name('create');
    Route::post('/',              [RoomController::class, 'store'])->name('store');
    Route::get('/{room}',         [RoomController::class, 'show'])->name('show');
    Route::get('/{room}/edit',    [RoomController::class, 'edit'])->name('edit');
    Route::put('/{room}',         [RoomController::class, 'update'])->name('update');
    Route::patch('/{room}/status',[RoomController::class, 'updateStatus'])->name('update-status');

    // Lịch phân bổ ca (DoctorSchedules)
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/',                       [RoomController::class, 'scheduleIndex'])->name('index');
        Route::get('/create',                 [RoomController::class, 'createSchedule'])->name('create');
        Route::post('/',                      [RoomController::class, 'storeSchedule'])->name('store');
        Route::get('/{schedule}/edit',        [RoomController::class, 'editSchedule'])->name('edit');
        Route::put('/{schedule}',             [RoomController::class, 'updateSchedule'])->name('update');
        Route::delete('/{schedule}',          [RoomController::class, 'destroySchedule'])->name('destroy');
    });
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_admin'])->group(function () {

    // ── Dịch vụ & Bảng giá ─────────────────────────────────────
    Route::resource('services',  ServiceController::class);
    Route::patch('services/{service}/toggle-status', [ ServiceController::class, 'toggleStatus'])
        ->name('services.toggle-status');

    // Bảng giá
    Route::post('services/{service}/prices',            [ ServiceController::class, 'storePrice'])
        ->name('services.prices.store');
    Route::put('services/{service}/prices/{price}',     [ ServiceController::class, 'updatePrice'])
        ->name('services.prices.update');
    Route::delete('services/{service}/prices/{price}',  [ ServiceController::class, 'destroyPrice'])
        ->name('services.prices.destroy');

    // ── Phòng khám ──────────────────────────────────────────────
    Route::resource('rooms',  RoomController::class);
    Route::patch('rooms/{room}/status', [ RoomController::class, 'updateStatus'])
        ->name('rooms.update-status');

    // Ca trực
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('schedule',                        [ RoomController::class, 'scheduleIndex'])  ->name('schedule.index');
        Route::get('schedule/create',                 [ RoomController::class, 'createSchedule']) ->name('schedule.create');
        Route::post('schedule',                       [ RoomController::class, 'storeSchedule'])  ->name('schedule.store');
        Route::get('schedule/{schedule}/edit',        [ RoomController::class, 'editSchedule'])   ->name('schedule.edit');
        Route::put('schedule/{schedule}',             [ RoomController::class, 'updateSchedule']) ->name('schedule.update');
        Route::delete('schedule/{schedule}',          [ RoomController::class, 'destroySchedule'])->name('schedule.destroy');
        // AJAX conflict check
        Route::get('schedule/check-conflict',         [ RoomController::class, 'checkConflict'])  ->name('schedule.check-conflict');
    });
});
