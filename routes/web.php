<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReviewsDoctorController;
use App\Http\Controllers\Admin\BhytController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\User\ServiceController as UserServiceController;
use App\Http\Controllers\User\PaymentController as UserPaymentController;
use App\Http\Controllers\tiensucontroler;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\HomeController;

// ============================================================
// TRANG CHỦ & AUTH
// ============================================================

Route::get('/', [UserServiceController::class, 'index'])->name('home');

Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

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

    // API gợi ý (AJAX)
    Route::get('/api/appointments/suggest',    [AppointmentController::class, 'suggest'])->name('appointments.suggest');
    Route::get('/api/appointments/timeslots',  [AppointmentController::class, 'timeslots'])->name('appointments.timeslots');
    Route::get('/api/appointments/queue-info', [AppointmentController::class, 'getQueueInfo'])->name('appointments.queue-info');

    // Lịch sử + dời/hủy lịch hẹn
    Route::prefix('lich-hen')->name('user.appointments.')->group(function () {
        Route::get('/',          [AppointmentController::class, 'index'])->name('index');
        Route::get('/{id}/doi',  [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{id}/doi',  [AppointmentController::class, 'update'])->name('update');
        Route::post('/{id}/huy', [AppointmentController::class, 'cancel'])->name('cancel');
        Route::get('/',          [AppointmentController::class, 'index'])->name('index');
    });

    // --------------------------------------------------------
    // THANH TOÁN (User)
    // Prefix: /payments  |  Name prefix: user.payments.
    // --------------------------------------------------------
    Route::prefix('payments')->name('user.payments.')->group(function () {

        // !! Đặt /history TRƯỚC /{paymentId} để tránh conflict !!
        Route::get('/history', [UserPaymentController::class, 'history'])
            ->name('history');

        // Trang chọn phương thức thanh toán cho 1 lịch hẹn
        Route::get('/appointment/{appointmentId}', [UserPaymentController::class, 'show'])
            ->name('show');

        // Khởi tạo giao dịch (POST từ form chọn phương thức)
        Route::post('/store', [UserPaymentController::class, 'store'])
            ->name('store');

        // Trang quét QR
        Route::get('/{paymentId}/qr', [UserPaymentController::class, 'qr'])
            ->name('qr');

        // Trang giả lập cổng thanh toán (ATM / MoMo / ZaloPay)
        Route::get('/{paymentId}/gateway', [UserPaymentController::class, 'gateway'])
            ->name('gateway');

        // Xác nhận thanh toán thành công
        Route::post('/{paymentId}/confirm', [UserPaymentController::class, 'confirm'])
            ->name('confirm');

        // Hủy / thất bại
        // GET  → dùng cho link "Hủy giao dịch" trong gateway.blade.php
        // POST → dùng cho fetch() trong qr.blade.php (timeout / nút hủy)
        Route::get('/{paymentId}/fail',  [UserPaymentController::class, 'fail'])
            ->name('fail');
        Route::post('/{paymentId}/fail', [UserPaymentController::class, 'fail'])
            ->name('fail.post');

        // Trang kết quả / biên lai
        Route::get('/{paymentId}/success', [UserPaymentController::class, 'success'])
            ->name('success');
    });

    // --------------------------------------------------------
    // Đánh giá bác sĩ
    // --------------------------------------------------------
    Route::get('/reviews/check',           [ReviewsDoctorController::class, 'checkCanReview'])->name('reviews.check');
    Route::post('/reviews',                [ReviewsDoctorController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}',        [ReviewsDoctorController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}',     [ReviewsDoctorController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/reply', [ReviewsDoctorController::class, 'reply'])->name('reviews.reply');

    // Xem tiền sử bệnh
    Route::get('/tiensu',  [tiensucontroler::class, 'tiensusuckhoe'])->name('tiensu.index');
    Route::post('/tiensu', [tiensucontroler::class, 'luutiensu'])->name('tiensu.store');

    // Thẻ thành viên
    Route::get('/thethanhvien', [MembershipController::class, 'show'])->name('membership.show');

    // Trang chủ sau khi đăng nhập
    Route::get('/trangchu', [HomeController::class, 'index'])->name('Home.trangchu');
});

// ============================================================
// ADMIN ROUTES (Yêu cầu đăng nhập + quyền is_admin)
// ============================================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_admin'])->group(function () {

    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // --------------------------------------------------------
    // Quản lý DỊCH VỤ (CRUD + bảng giá)
    // --------------------------------------------------------
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
        Route::post('/{service}/prices',           [AdminServiceController::class, 'storePrice'])->name('prices.store');
        Route::put('/{service}/prices/{price}',    [AdminServiceController::class, 'updatePrice'])->name('prices.update');
        Route::delete('/{service}/prices/{price}', [AdminServiceController::class, 'destroyPrice'])->name('prices.destroy');
    });

    // --------------------------------------------------------
    // Quản lý PHÒNG KHÁM + LỊCH TRỰC
    // --------------------------------------------------------
    Route::prefix('rooms')->name('rooms.')->group(function () {

        // Lịch tuần
        Route::get('/weekly',          [RoomController::class, 'weeklySchedule'])->name('weekly');
        Route::get('/weekly/{roomId}', [RoomController::class, 'weeklySchedule'])->name('weekly.room');
        Route::get('/weekly-ajax',     [RoomController::class, 'weeklyScheduleAjax'])->name('weekly.ajax');

        // Quản lý ca trực
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/all',             [RoomController::class, 'scheduleAll'])->name('all');
            Route::get('/',                [RoomController::class, 'scheduleIndex'])->name('index');
            Route::get('/create',          [RoomController::class, 'createSchedule'])->name('create');
            Route::post('/',               [RoomController::class, 'storeSchedule'])->name('store');
            Route::get('/{schedule}/edit', [RoomController::class, 'editSchedule'])->name('edit');
            Route::put('/{schedule}',      [RoomController::class, 'updateSchedule'])->name('update');
            Route::delete('/{schedule}',   [RoomController::class, 'destroySchedule'])->name('destroy');
            Route::get('/check-conflict',  [RoomController::class, 'checkConflict'])->name('check-conflict');
        });

        // CRUD phòng (đặt SAU prefix để tránh conflict)
        Route::get('/',            [RoomController::class, 'index'])->name('index');
        Route::get('/create',      [RoomController::class, 'create'])->name('create');
        Route::post('/',           [RoomController::class, 'store'])->name('store');
        Route::get('/{room}',      [RoomController::class, 'show'])->name('show');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit');
        Route::put('/{room}',      [RoomController::class, 'update'])->name('update');
        Route::patch('/{room}/status', [RoomController::class, 'updateStatus'])->name('update-status');
    });

    // THANH TOÁN (Admin)
    Route::prefix('payments')->name('payments.')->group(function () {

        // Danh sách giao dịch
        Route::get('/', [PaymentController::class, 'index'])->name('index');

        // Trang thanh toán hóa đơn (checkout)
        Route::get('/checkout/{invoiceId}', [PaymentController::class, 'checkout'])->name('checkout');

        // Chi tiết giao dịch theo paymentId
        Route::get('/{paymentId}', [PaymentController::class, 'show'])->name('show');

        // Tạo giao dịch
        Route::post('/store', [PaymentController::class, 'store'])->name('store');

        // Trang QR
        Route::get('/{paymentId}/qr', [PaymentController::class, 'qr'])->name('qr');

        // Xác nhận thanh toán
        Route::post('/{paymentId}/confirm', [PaymentController::class, 'confirm'])->name('confirm');

        // Đánh dấu thất bại
        Route::post('/{paymentId}/fail', [PaymentController::class, 'fail'])->name('fail');
    });
    // --------------------------------------------------------
    // Quản lý BHYT
    // --------------------------------------------------------
    Route::prefix('bhyt')->name('bhyt.')->group(function () {
        Route::get('/',        [BhytController::class, 'index'])->name('index');
        Route::post('/lookup', [BhytController::class, 'lookup'])->name('lookup');
        Route::post('/apply',  [BhytController::class, 'apply'])->name('apply');
    });

    // --------------------------------------------------------
    // Thống kê bác sĩ & Doanh thu
    // --------------------------------------------------------
    Route::get('/doctor-statistics', [\App\Http\Controllers\Admin\DoctorStatisticController::class, 'index'])->name('doctor-statistics.index');
    Route::get('/revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])->name('revenue.index');

    // --------------------------------------------------------
    // Quản lý tiêm chủng
    // --------------------------------------------------------
    Route::resource('vaccines', \App\Http\Controllers\Admin\VaccineController::class);
    Route::resource('vaccination-records', \App\Http\Controllers\Admin\VaccinationRecordController::class);

});

// ============================================================
// ROUTE BÁC SĨ (tạm thời)
// ============================================================

Route::get('/bac-si', function () {
    return view('welcome');
})->name('doctors.index');
