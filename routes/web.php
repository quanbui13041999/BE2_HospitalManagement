<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\User\ServiceController as UserServiceController;
use App\Http\Controllers\HealthBackgroundController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
// ============================================================
// TRANG CHỦ & AUTH
// ============================================================

Route::get('/', [App\Http\Controllers\User\ServiceController::class, 'index'])->name('home');

Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// ============================================================
// USER ROUTES (Công khai & Có đăng nhập)
// ============================================================

// Dịch vụ công khai (không cần đăng nhập)
Route::prefix('dich-vu')->name('user.services.')->controller(UserServiceController::class)->group(function () {
    Route::get('/',        'index')->name('index');
    Route::get('/{id}',    'show')->name('show');
    Route::get('/{id}/gia/{priceType}', 'getPrice')->name('get-price');
});

Route::redirect('/services', '/dich-vu', 301);

// Đặt lịch & quản lý lịch hẹn (cần đăng nhập)
Route::middleware('auth')->group(function () {

    // Đặt lịch mới
    Route::prefix('dat-lich')->name('appointments.')->group(function () {
        Route::get('/',           [AppointmentController::class, 'create'])->name('create');
        Route::post('/',          [AppointmentController::class, 'store'])->name('store');
        Route::get('/schedules',  [AppointmentController::class, 'getSchedules'])->name('schedules');
    });

    // API gợi ý (AJAX) – đặt tên là appointments.suggest và appointments.timeslots
    Route::get('/api/appointments/suggest',   [AppointmentController::class, 'suggest'])->name('appointments.suggest');
    Route::get('/api/appointments/timeslots', [AppointmentController::class, 'timeslots'])->name('appointments.timeslots');
    Route::get('/api/appointments/queue-info', [AppointmentController::class, 'getQueueInfo'])->name('appointments.queue-info');

    // Lịch sử + dời/hủy lịch hẹn
    Route::prefix('lich-hen')->name('appointments.')->group(function () {
        Route::get('/',                 [AppointmentController::class, 'index'])->name('index');
        Route::get('/{id}/doi',         [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{id}/doi',         [AppointmentController::class, 'update'])->name('update');
        Route::post('/{id}/huy',        [AppointmentController::class, 'cancel'])->name('cancel');
    });

    //xem tien su
    Route::middleware('auth')->group(function () {
        Route::get('/health-background', [HealthBackgroundController::class, 'index'])->name('health.index');
        Route::post('/health-background', [HealthBackgroundController::class, 'store'])->name('health.store');
    });
    //thanh vien uu dai
    Route::get('/thethanhvien', [MembershipController::class, 'show'])->name('membership.show');
});

// ============================================================
// ADMIN ROUTES (Yêu cầu đăng nhập + quyền is_admin)
// ============================================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_admin'])->group(function () {

    // Dashboard
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Quản lý DỊCH VỤ (CRUD + bảng giá)
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/',               [AdminServiceController::class, 'index'])->name('index');
        Route::get('/create',         [AdminServiceController::class, 'create'])->name('create');
        Route::post('/',              [AdminServiceController::class, 'store'])->name('store');
        Route::get('/{service}',      [AdminServiceController::class, 'show'])->name('show');
        Route::get('/{service}/edit', [AdminServiceController::class, 'edit'])->name('edit');
        Route::put('/{service}',      [AdminServiceController::class, 'update'])->name('update');
        Route::delete('/{service}',   [AdminServiceController::class, 'destroy'])->name('destroy');
        Route::patch('/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus'])->name('toggle-status');

        // Bảng giá (nested)
        Route::post('/{service}/prices',            [AdminServiceController::class, 'storePrice'])->name('prices.store');
        Route::put('/{service}/prices/{price}',     [AdminServiceController::class, 'updatePrice'])->name('prices.update');
        Route::delete('/{service}/prices/{price}',  [AdminServiceController::class, 'destroyPrice'])->name('prices.destroy');
    });

    // Quản lý PHÒNG KHÁM + LỊCH TRỰC
    Route::prefix('rooms')->name('rooms.')->group(function () {

        // LỊCH TUẦN (Weekly Schedule)
        Route::get('/weekly',           [RoomController::class, 'weeklySchedule'])->name('weekly');
        Route::get('/weekly/{roomId}',  [RoomController::class, 'weeklySchedule'])->name('weekly.room');
        Route::get('/weekly-ajax',      [RoomController::class, 'weeklyScheduleAjax'])->name('weekly.ajax');

        // Quản lý CA TRỰC (DoctorSchedules)
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/all',          [RoomController::class, 'scheduleAll'])->name('all');
            Route::get('/',             [RoomController::class, 'scheduleIndex'])->name('index');
            Route::get('/create',       [RoomController::class, 'createSchedule'])->name('create');
            Route::post('/',            [RoomController::class, 'storeSchedule'])->name('store');
            Route::get('/{schedule}/edit', [RoomController::class, 'editSchedule'])->name('edit');
            Route::put('/{schedule}',   [RoomController::class, 'updateSchedule'])->name('update');
            Route::delete('/{schedule}', [RoomController::class, 'destroySchedule'])->name('destroy');
            Route::get('/check-conflict', [RoomController::class, 'checkConflict'])->name('check-conflict');
        });

        // CRUD PHÒNG (đặt SAU các route có prefix để tránh conflict)
        Route::get('/',          [RoomController::class, 'index'])->name('index');
        Route::get('/create',    [RoomController::class, 'create'])->name('create');
        Route::post('/',         [RoomController::class, 'store'])->name('store');
        Route::get('/{room}',    [RoomController::class, 'show'])->name('show');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit');
        Route::put('/{room}',    [RoomController::class, 'update'])->name('update');
        Route::patch('/{room}/status', [RoomController::class, 'updateStatus'])->name('update-status');
    });
});

// ============================================================
// ROUTE TẠM (sẽ xóa sau khi có controller thật)
// ============================================================
Route::get('/bac-si', function () {
    return view('welcome');
})->name('doctors.index');
// liên hệ khẩn cấp
Route::middleware(['auth'])->group(function () {

    Route::get('/lien-he-khan-cap', [EmergencyContactController::class, 'index'])
        ->name('emergency-contacts.index');

    Route::post('/lien-he-khan-cap', [EmergencyContactController::class, 'store'])
        ->name('emergency-contacts.store');
});
//profile 
Route::middleware(['auth'])->prefix('profile')->name('profile.')->group(function () {

    Route::get('/',          [ProfileController::class, 'show'])->name('show');      // → /profile

    Route::get('/edit',      [ProfileController::class, 'edit'])->name('edit');      // → /profile/edit
    Route::put('/update',    [ProfileController::class, 'update'])->name('update');  // → /profile/update

    Route::get('/password',  [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::put('/password',  [ProfileController::class, 'updatePassword'])->name('password.update');

    Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
});
/*
|--------------------------------------------------------------------------
|  Lưu Trữ & Tra Cứu Tài Liệu Y Khoa Cá Nhân
|--------------------------------------------------------------------------
*/
 
Route::prefix('tai-lieu')->name('documents.')->group(function () {
 
    // Danh sách + tìm kiếm + lọc
    Route::get('/',                      [DocumentController::class, 'index'])    ->name('index');
 
    // Upload file mới
    Route::post('/',                     [DocumentController::class, 'store'])    ->name('store');
 
    // Xem file inline (PDF / ảnh)
    Route::get('/{document}/view',       [DocumentController::class, 'show'])     ->name('show');
 
    // Tải file về máy
    Route::get('/{document}/download',   [DocumentController::class, 'download']) ->name('download');
 
    // Form chỉnh sửa metadata
    Route::get('/{document}/edit',       [DocumentController::class, 'edit'])     ->name('edit');
 
    // Lưu chỉnh sửa metadata
    Route::put('/{document}',            [DocumentController::class, 'update'])   ->name('update');
 
    // Xoá tài liệu
    Route::delete('/{document}',         [DocumentController::class, 'destroy'])  ->name('destroy');
});
 
// // Redirect root về danh sách
// Route::redirect('tai-lieu', '/documents');
