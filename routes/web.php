<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Admin\BhytController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;

// ============================================================
// TRANG CHỦ & AUTH
// ============================================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

// ============================================================
// USER ROUTES (Dành cho bệnh nhân - không cần đăng nhập vẫn xem được)
// ============================================================

Route::prefix('dich-vu')->name('user.services.')->controller(UserServiceController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{id}', 'show')->name('show');
    Route::get('/{id}/gia/{priceType}', 'getPrice')->name('get-price');
});

Route::middleware('auth')->prefix('dat-lich')->name('appointments.')->group(function () {
    Route::get('/',           [AppointmentController::class, 'create'])->name('create');
    Route::post('/',          [AppointmentController::class, 'store'])->name('store');
    Route::get('/schedules',  [AppointmentController::class, 'getSchedules'])->name('schedules');
});

Route::middleware('auth')->prefix('lich-hen')->name('appointments.')->group(function () {
    Route::get('/',                 [AppointmentController::class, 'index'])->name('index');
    Route::get('/{id}/doi',         [AppointmentController::class, 'edit'])->name('edit');
    Route::put('/{id}/doi',         [AppointmentController::class, 'update'])->name('update');
    Route::post('/{id}/huy',        [AppointmentController::class, 'cancel'])->name('cancel');
});

Route::redirect('/services', '/dich-vu', 301);

// ============================================================
// ADMIN ROUTES (Chỉ dành cho quản trị viên)
// ============================================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_admin'])->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/',               [ServiceController::class, 'index'])->name('index');
        Route::get('/create',         [ServiceController::class, 'create'])->name('create');
        Route::post('/',              [ServiceController::class, 'store'])->name('store');
        Route::get('/{service}',      [ServiceController::class, 'show'])->name('show');
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
        Route::put('/{service}',      [ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}',   [ServiceController::class, 'destroy'])->name('destroy');
        Route::patch('/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{service}/prices',             [ServiceController::class, 'storePrice'])->name('prices.store');
        Route::put('/{service}/prices/{price}',      [ServiceController::class, 'updatePrice'])->name('prices.update');
        Route::delete('/{service}/prices/{price}',   [ServiceController::class, 'destroyPrice'])->name('prices.destroy');
    });

    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/weekly', [RoomController::class, 'weeklySchedule'])->name('weekly');
        Route::get('/weekly/{roomId}', [RoomController::class, 'weeklySchedule'])->name('weekly.room');
        Route::get('/weekly-ajax', [RoomController::class, 'weeklyScheduleAjax'])->name('weekly.ajax');

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

        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::get('/create', [RoomController::class, 'create'])->name('create');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/{room}', [RoomController::class, 'show'])->name('show');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit');
        Route::put('/{room}', [RoomController::class, 'update'])->name('update');
        Route::patch('/{room}/status', [RoomController::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                         [PaymentController::class, 'index'])->name('index');
        Route::get('/invoice/{invoiceId}',      [PaymentController::class, 'show'])->name('show');
        Route::post('/',                        [PaymentController::class, 'store'])->name('store');
        Route::get('/{paymentId}/qr',           [PaymentController::class, 'qr'])->name('qr');
        Route::post('/{paymentId}/confirm',     [PaymentController::class, 'confirm'])->name('confirm');
        Route::post('/{paymentId}/fail',        [PaymentController::class, 'fail'])->name('fail');
    });

    Route::prefix('bhyt')->name('bhyt.')->group(function () {
        Route::get('/',         [BhytController::class, 'index'])->name('index');
        Route::post('/lookup',  [BhytController::class, 'lookup'])->name('lookup');
        Route::post('/apply',   [BhytController::class, 'apply'])->name('apply');
    });
});

Route::get('/bac-si', function () {
    return view('welcome');
})->name('doctors.index');
