<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\User\ServiceController as UserServiceController;

// ============================================================
// TRANG CHỦ & AUTH
// ============================================================

// Trang chủ
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication
Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

// ============================================================
// USER ROUTES (Dành cho bệnh nhân - không cần đăng nhập vẫn xem được)
// ============================================================

// User xem dịch vụ (ai cũng xem được)
Route::prefix('dich-vu')->name('user.services.')->controller(UserServiceController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{id}', 'show')->name('show');
    Route::get('/{id}/gia/{priceType}', 'getPrice')->name('get-price');
});

// User đặt lịch (cần đăng nhập)
Route::middleware('auth')->prefix('dat-lich')->name('appointments.')->group(function () {
    Route::get('/',           [AppointmentController::class, 'create'])->name('create');
    Route::post('/',          [AppointmentController::class, 'store'])->name('store');
    Route::get('/schedules',  [AppointmentController::class, 'getSchedules'])->name('schedules');
});

// User xem lịch sử lịch hẹn (cần đăng nhập)
Route::middleware('auth')->prefix('lich-hen')->name('appointments.')->group(function () {
    Route::get('/',                 [AppointmentController::class, 'index'])->name('index');
    Route::get('/{id}/doi',         [AppointmentController::class, 'edit'])->name('edit');
    Route::put('/{id}/doi',         [AppointmentController::class, 'update'])->name('update');
    Route::post('/{id}/huy',        [AppointmentController::class, 'cancel'])->name('cancel');
});

// Redirect cũ
Route::redirect('/services', '/dich-vu', 301);

// ============================================================
// ADMIN ROUTES (Chỉ dành cho quản trị viên)
// ============================================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_admin'])->group(function () {

    // Dashboard
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // ============================================================
    // QUẢN LÝ DỊCH VỤ (CRUD đầy đủ)
    // ============================================================
    Route::prefix('services')->name('services.')->group(function () {
        // Danh sách & CRUD cơ bản
        Route::get('/',               [ServiceController::class, 'index'])->name('index');
        Route::get('/create',         [ServiceController::class, 'create'])->name('create');
        Route::post('/',              [ServiceController::class, 'store'])->name('store');
        Route::get('/{service}',      [ServiceController::class, 'show'])->name('show');
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
        Route::put('/{service}',      [ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}',   [ServiceController::class, 'destroy'])->name('destroy');

        // Chức năng bổ sung
        Route::patch('/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('toggle-status');

        // Quản lý bảng giá (nested)
        Route::post('/{service}/prices',             [ServiceController::class, 'storePrice'])->name('prices.store');
        Route::put('/{service}/prices/{price}',      [ServiceController::class, 'updatePrice'])->name('prices.update');
        Route::delete('/{service}/prices/{price}',   [ServiceController::class, 'destroyPrice'])->name('prices.destroy');
    });

    // ============================================================
    // QUẢN LÝ PHÒNG KHÁM
    // ============================================================
    Route::prefix('rooms')->name('rooms.')->group(function () {

        // ✅ QUAN TRỌNG: Đặt PREFIX 'schedule' TRƯỚC route '/{room}' để tránh conflict
        // Quản lý lịch phân bổ ca (DoctorSchedules)
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/all', [RoomController::class, 'scheduleAll'])->name('all');
            Route::get('/', [RoomController::class, 'scheduleIndex'])->name('index');
            Route::get('/create', [RoomController::class, 'createSchedule'])->name('create');
            Route::post('/', [RoomController::class, 'storeSchedule'])->name('store');
            Route::get('/{schedule}/edit', [RoomController::class, 'editSchedule'])->name('edit');
            Route::put('/{schedule}', [RoomController::class, 'updateSchedule'])->name('update');
            Route::delete('/{schedule}', [RoomController::class, 'destroySchedule'])->name('destroy');
            Route::get('/check-conflict', [RoomController::class, 'checkConflict'])->name('check-conflict');
        });

        // CRUD phòng - Đặt SAU schedule để không bị Laravel hiểu 'schedule' là tham số {room}
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::get('/create', [RoomController::class, 'create'])->name('create');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/{room}', [RoomController::class, 'show'])->name('show');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit');
        Route::put('/{room}', [RoomController::class, 'update'])->name('update');
        Route::patch('/{room}/status', [RoomController::class, 'updateStatus'])->name('update-status');
    });
});
// Trong group admin, thêm route này
Route::prefix('rooms')->name('rooms.')->group(function () {

    // LỊCH TUẦN CHO 1 PHÒNG - Đặt TRƯỚC route /{room}
    Route::get('/weekly', [RoomController::class, 'weeklySchedule'])->name('weekly');
    Route::get('/weekly/{roomId}', [RoomController::class, 'weeklySchedule'])->name('weekly.room');

    // Quản lý lịch phân bổ ca
    Route::prefix('schedule')->name('schedule.')->group(function () {
        // ... existing routes ...
    });

    // CRUD phòng - Đặt SAU
    Route::get('/', [RoomController::class, 'index'])->name('index');
    // ... other routes ...
});
// ============================================================
// TẠM THỜI (sẽ xóa sau khi có controller thật)
// ============================================================
Route::get('/bac-si', function () {
    return view('welcome');
})->name('doctors.index');
// Trong group admin, thêm route này
Route::prefix('rooms')->name('rooms.')->group(function () {
    // ... existing routes ...
    
    // AJAX lấy lịch tuần
    Route::get('/weekly-ajax', [RoomController::class, 'weeklyScheduleAjax'])->name('weekly.ajax');
});
Route::prefix('rooms')->name('rooms.')->group(function () {
    // ... other routes ...
    
    // AJAX lấy lịch tuần
    Route::get('/weekly-ajax', [RoomController::class, 'weeklyScheduleAjax'])->name('weekly.ajax');
});
