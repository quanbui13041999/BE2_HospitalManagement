<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Doctor\ReviewsDoctorController;
use App\Http\Controllers\Admin\BhytController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\DeviceTypeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\ServiceController as UserServiceController;
use App\Http\Controllers\User\PaymentController as UserPaymentController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HealthBackgroundController;
use App\Http\Controllers\HealthTrackingController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Doctor\DoctorScheduleController;
use App\Http\Controllers\Doctor\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AdminNutritionController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicalHistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\PatientNutritionController;
use App\Http\Controllers\Admin\PatientSearchController;
use App\Http\Controllers\Admin\ActivityLogController;

use App\Http\Controllers\NewsController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\RehabExerciseController;
use App\Http\Controllers\Admin\AdminRehabExerciseController;

use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\ChatRoomController;

use App\Http\Controllers\Queue\{QueueManageController, QueueDoctorController, QueueDisplayController};
use App\Http\Controllers\Patient\TreatmentReminderController;
use App\Http\Controllers\Admin\DoctorStatisticController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\Admin\VaccineController;
use App\Http\Controllers\Admin\VaccinationRecordController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\TreatmentReminderAdminController;



// ============================================================
// TRANG CHỦ & AUTH
// ============================================================

Route::get('/', [HomeController::class, 'welcome'])->name('home');

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
    Route::get('/data',    'publicServicesData')->name('data');   // Realtime polling
    Route::get('/',        'index')->name('index');
    Route::get('/{id}',    'show')->name('show');
    Route::get('/{id}/gia/{priceType}', 'getPrice')->name('get-price');
});

Route::redirect('/services', '/dich-vu', 301);

// Tin tức công khai (không cần đăng nhập)
Route::get('/news',      [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{id}', [NewsController::class, 'show'])->name('news.show');

// Trang bác sĩ
Route::get('/bac-si', [HomeController::class, 'welcome'])->name('doctors.index');

// ============================================================
// USER ROUTES (Yêu cầu đăng nhập)
// ============================================================

Route::middleware('auth')->group(function () {

    // Trang chủ sau khi đăng nhập
    Route::get('/trangchu', [HomeController::class, 'index'])->name('Home.trangchu');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/dropdown', [NotificationController::class, 'dropdown'])->name('dropdown');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('mark-read');
    });

    // --------------------------------------------------------
    // Đặt lịch mới
    // --------------------------------------------------------
    Route::prefix('dat-lich')->name('appointments.')->group(function () {
        Route::get('/',          [AppointmentController::class, 'create'])->name('create');
        Route::post('/',         [AppointmentController::class, 'store'])->name('store');
        Route::get('/schedules', [AppointmentController::class, 'getSchedules'])->name('schedules');
    });

    // Đăng ký dịch vụ & Thanh toán riêng lẻ (không qua bác sĩ)
    Route::post('/dich-vu/{id}/dat-mua', [UserServiceController::class, 'bookService'])->name('user.services.book');

    // API gợi ý (AJAX)
    Route::get('/api/appointments/suggest',    [AppointmentController::class, 'suggest'])->name('appointments.suggest');
    Route::get('/api/appointments/timeslots',  [AppointmentController::class, 'timeslots'])->name('appointments.timeslots');
    Route::get('/api/appointments/queue-info', [AppointmentController::class, 'getQueueInfo'])->name('appointments.queue-info');

    // --------------------------------------------------------
    // Lịch hẹn — Route chính (user.appointments.*)
    // --------------------------------------------------------
    Route::prefix('lich-hen')->name('user.appointments.')->group(function () {
        Route::get('/',                  [AppointmentController::class, 'index'])->name('index');
        Route::get('/{id}/bac-si-nghi', [AppointmentController::class, 'doctorOff'])->name('doctor-off');
        Route::get('/{id}/doi',          [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{id}/doi',          [AppointmentController::class, 'update'])->name('update');
        Route::post('/{id}/huy',         [AppointmentController::class, 'cancel'])->name('cancel');
    });

    // ALIAS: Route cũ cho tương thích với view (appointments.*)
    Route::prefix('lich-hen')->name('appointments.')->group(function () {
        Route::get('/',                  [AppointmentController::class, 'index'])->name('index');
        Route::get('/{id}/bac-si-nghi', [AppointmentController::class, 'doctorOff'])->name('doctor-off');
        Route::get('/{id}/doi',          [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{id}/doi',          [AppointmentController::class, 'update'])->name('update');
        Route::post('/{id}/huy',         [AppointmentController::class, 'cancel'])->name('cancel');
    });

    // --------------------------------------------------------
    // Thanh toán (Users)
    // --------------------------------------------------------
    Route::prefix('payments')->name('user.payments.')->group(function () {
        Route::get('/history',                          [UserPaymentController::class, 'history'])->name('history');
        Route::get('/appointment/{appointmentId}',      [UserPaymentController::class, 'show'])->name('show');
        Route::post('/store',                           [UserPaymentController::class, 'store'])->name('store');
        Route::get('/{paymentId}/qr',                  [UserPaymentController::class, 'qr'])->name('qr');
        Route::get('/{paymentId}/gateway',             [UserPaymentController::class, 'gateway'])->name('gateway');
        Route::get('/{paymentId}/check',               [UserPaymentController::class, 'check'])->name('check');
        Route::post('/{paymentId}/confirm',             [UserPaymentController::class, 'confirm'])->name('confirm');
        Route::get('/{paymentId}/fail',                [UserPaymentController::class, 'fail'])->name('fail');
        Route::post('/{paymentId}/fail',               [UserPaymentController::class, 'fail'])->name('fail.post');
        Route::get('/{paymentId}/success',             [UserPaymentController::class, 'success'])->name('success');
    });

    // --------------------------------------------------------
    // Đánh giá bác sĩ
    // --------------------------------------------------------
    Route::get('/reviews/check',            [ReviewsDoctorController::class, 'checkCanReview'])->name('reviews.check');
    Route::post('/reviews',                 [ReviewsDoctorController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}',         [ReviewsDoctorController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}',      [ReviewsDoctorController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/reply',  [ReviewsDoctorController::class, 'reply'])->name('reviews.reply');

    // --------------------------------------------------------
    // Thẻ thành viên
    // --------------------------------------------------------
    Route::get('/thethanhvien', [MembershipController::class, 'show'])->name('membership.show');

    // --------------------------------------------------------
    // Thẻ BHYT (User view)
    // --------------------------------------------------------
    Route::get('/my-insurance', [BhytController::class, 'userInsurance'])->name('user.insurance');

    // --------------------------------------------------------
    // Health Background
    // --------------------------------------------------------
    Route::get('/health-background',  [HealthBackgroundController::class, 'index'])->name('health.index');
    Route::post('/health-background', [HealthBackgroundController::class, 'store'])->name('health.store');

    // --------------------------------------------------------
    // Liên hệ khẩn cấp
    // --------------------------------------------------------
    Route::get('/lien-he-khan-cap',  [EmergencyContactController::class, 'index'])->name('emergency-contacts.index');
    Route::post('/lien-he-khan-cap', [EmergencyContactController::class, 'store'])->name('emergency-contacts.store');

    // --------------------------------------------------------
    // Profile
    // --------------------------------------------------------
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',          [ProfileController::class, 'show'])->name('show');
        Route::get('/edit',      [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update',    [ProfileController::class, 'update'])->name('update');
        Route::get('/password',  [ProfileController::class, 'editPassword'])->name('password.edit');
        Route::put('/password',  [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
    });

    // --------------------------------------------------------
    // Tài liệu y khoa
    // --------------------------------------------------------
    Route::prefix('tai-lieu')->name('documents.')->group(function () {
        Route::get('/',                    [DocumentController::class, 'index'])->name('index');
        Route::post('/',                   [DocumentController::class, 'store'])->name('store');
        Route::get('/{document}/view',     [DocumentController::class, 'show'])->name('show')->whereNumber('document');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download')->whereNumber('document');
        Route::get('/{document}/edit',     [DocumentController::class, 'edit'])->name('edit')->whereNumber('document');
        Route::put('/{document}',          [DocumentController::class, 'update'])->name('update')->whereNumber('document');
        Route::delete('/{document}',       [DocumentController::class, 'destroy'])->name('destroy')->whereNumber('document');
    });

    // --------------------------------------------------------
    // Chat CSKH – Patient Routes
    // --------------------------------------------------------
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/',                        [ChatController::class, 'index'])->name('index');
        Route::post('/room',                   [ChatController::class, 'getOrCreateRoom'])->name('room');
        Route::get('/messages/{roomId}',       [ChatController::class, 'getMessages'])->name('messages');
        Route::post('/send',                   [ChatController::class, 'sendMessage'])->name('send');
        Route::delete('/messages/{messageId}', [ChatController::class, 'recallMessage'])->name('recall');
    });

    // --------------------------------------------------------
    // Doctor specific routes (patient-facing)
    // --------------------------------------------------------
    Route::prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/appointments', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
    });
});

// ============================================================
// HỆ THỐNG NHẮC NHỞ TUÂN THỦ ĐIỀU TRỊ – Patient
// ============================================================


Route::middleware(['auth'])->prefix('treatment')->name('treatment.')->group(function () {
    Route::get('/',                    [TreatmentReminderController::class, 'index'])->name('index');
    Route::post('/confirm/{reminder}', [TreatmentReminderController::class, 'confirm'])->name('confirm')->whereNumber('reminder');
    Route::post('/instruction/toggle', [TreatmentReminderController::class, 'toggleInstruction'])->name('instruction.toggle');
    Route::get('/report',              [TreatmentReminderController::class, 'report'])->name('report');
});

// ============================================================
// ROUTE BÁC SĨ (Doctor Dashboard + Schedule Management)
// ============================================================

Route::prefix('doctor')->name('doctor.')->middleware('auth')->group(function () {
    Route::get('/dashboard',                                [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/appointments/today',             [DashboardController::class, 'todayAppointments']);
    Route::get('/dashboard/appointments/upcoming',          [DashboardController::class, 'upcomingAppointments']);
    Route::get('/dashboard/stats',                          [DashboardController::class, 'stats']);
    Route::patch('/dashboard/appointments/{id}/complete',   [DashboardController::class, 'completeAppointment']);
    Route::patch('/dashboard/appointments/{id}/cancel',     [DashboardController::class, 'cancelAppointment']);
    Route::get('/dashboard/reviews',                        [DashboardController::class, 'reviews']);
    Route::post('/dashboard/reviews/{id}/reply',            [DashboardController::class, 'replyReview']);
    Route::delete('/dashboard/reviews/{id}/reply',          [DashboardController::class, 'deleteReply']);
    Route::get('/dashboard/doctors/list',                   [DashboardController::class, 'doctorsList']);
    Route::post('/dashboard/doctors/upload-avatar',         [DashboardController::class, 'uploadAvatar']);
    Route::post('/dashboard/doctors',                       [DashboardController::class, 'storeDoctor']);
    Route::put('/dashboard/doctors/{id}',                   [DashboardController::class, 'updateDoctor']);
    Route::delete('/dashboard/doctors/{id}',                [DashboardController::class, 'destroyDoctor']);
});

Route::prefix('schedules')->name('doctor.')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('doctor.doctor-schedule');
    })->name('schedule');
});

// ============================================================
// API v1 – Doctor Schedules
// ============================================================

Route::prefix('api/v1')->middleware('auth')->group(function () {
    Route::prefix('schedules')->group(function () {
        // Recurring Schedule
        Route::post('recurring/preview',          [DoctorScheduleController::class, 'recurringPreview']);
        Route::post('recurring',                  [DoctorScheduleController::class, 'storeRecurring']);
        Route::get('recurring/{doctorId}',        [DoctorScheduleController::class, 'indexRecurring']);
        Route::delete('recurring/{scheduleId}',   [DoctorScheduleController::class, 'destroyRecurring']);

        // Day-Off (Block + Email)
        Route::post('day-off',                    [DoctorScheduleController::class, 'storeDayOff']);
        Route::get('day-off/{doctorId}',          [DoctorScheduleController::class, 'indexDayOff']);
        Route::delete('day-off/{scheduleId}',     [DoctorScheduleController::class, 'destroyDayOff']);

        // Utility
        Route::get('doctors',                     [DoctorScheduleController::class, 'listDoctors']);
    });
});

// ============================================================
// Medical Records and Medical History
// ============================================================

Route::prefix('medical-records')
    ->middleware(['auth'])
    ->name('medical-records.')
    ->group(function () {
        // CRUD cơ bản
        Route::get('/', [MedicalRecordController::class, 'index'])->name('index');
        Route::get('/create', [MedicalRecordController::class, 'create'])->name('create');
        Route::post('/', [MedicalRecordController::class, 'store'])->name('store');
        Route::get('/{id}', [MedicalRecordController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [MedicalRecordController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MedicalRecordController::class, 'update'])->name('update')->whereNumber('id');
        Route::delete('/{id}', [MedicalRecordController::class, 'destroy'])->name('destroy')->whereNumber('id');
        Route::get('/{id}/print', [MedicalRecordController::class, 'print'])->name('print')->whereNumber('id');

        // File đính kèm
        Route::post('/{id}/attachments', [MedicalRecordController::class, 'uploadAttachment'])->name('attachments.upload')->whereNumber('id');
        Route::get('/{recordId}/attachments/{attachmentId}/view', [MedicalRecordController::class, 'viewAttachment'])->name('attachments.view')->whereNumber('recordId')->whereNumber('attachmentId');
        Route::delete('/{recordId}/attachments/{attachmentId}', [MedicalRecordController::class, 'deleteAttachment'])->name('attachments.destroy')->whereNumber('recordId')->whereNumber('attachmentId');

        // Kết quả xét nghiệm (chỉ Doctor/Admin)
        Route::put('/{recordId}/orders/{orderId}/result', [MedicalRecordController::class, 'updateOrderResult'])->name('orders.update-result')->whereNumber('recordId')->whereNumber('orderId');
        Route::delete('/{recordId}/orders/{orderId}/result', [MedicalRecordController::class, 'deleteOrderResult'])->name('orders.delete-result')->whereNumber('recordId')->whereNumber('orderId');
    });

Route::middleware('auth')
    ->prefix('bac-si')
    ->name('doctor.')
    ->group(function () {
        Route::get('/lich-hen', [DoctorAppointmentController::class, 'index'])
            ->name('appointments.index');
    });

// Bác sĩ xem tiền sử & tài liệu của bệnh nhân
Route::middleware('auth')->group(function () {
    Route::get('/health/patient/{patientId}', [HealthBackgroundController::class, 'showPatient'])
        ->whereNumber('patientId')
        ->name('health.patient.show');

    Route::get('/documents/patient/{patientId}', [DocumentController::class, 'indexPatient'])
        ->whereNumber('patientId')
        ->name('documents.patient.index');
});

Route::prefix('medical_history')
    ->middleware(['auth'])
    ->name('medical_history.')
    ->group(function () {
        Route::get('/', [MedicalHistoryController::class, 'index'])->name('index');
    });

// ============================================================
// ADMIN ROUTES (Yêu cầu đăng nhập + quyền is_admin)
// ============================================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_admin'])->group(function () {

    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [AdminDashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

    // --------------------------------------------------------
    // Quản lý Dịch vụ (CRUD + bảng giá)
    // --------------------------------------------------------
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/data',           [AdminServiceController::class, 'servicesData'])->name('data');   // Realtime polling
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
    // Quản lý Phòng khám + Lịch trực
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
            Route::post('/auto-allocate',  [RoomController::class, 'autoAllocate'])->name('auto-allocate');
            Route::get('/{schedule}/edit', [RoomController::class, 'editSchedule'])->name('edit');
            Route::put('/{schedule}',      [RoomController::class, 'updateSchedule'])->name('update');
            Route::delete('/{schedule}',   [RoomController::class, 'destroySchedule'])->name('destroy');
            Route::get('/check-conflict',  [RoomController::class, 'checkConflict'])->name('check-conflict');
        });

        // CRUD phòng
        Route::get('/data',            [RoomController::class, 'roomsData'])->name('data');   // Realtime polling
        Route::get('/',                [RoomController::class, 'index'])->name('index');
        Route::get('/create',          [RoomController::class, 'create'])->name('create');
        Route::post('/',               [RoomController::class, 'store'])->name('store');
        Route::get('/{room}',          [RoomController::class, 'show'])->name('show');
        Route::get('/{room}/edit',     [RoomController::class, 'edit'])->name('edit');
        Route::put('/{room}',          [RoomController::class, 'update'])->name('update');
        Route::patch('/{room}/status', [RoomController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{room}',       [RoomController::class, 'destroy'])->name('destroy');
    });

    // --------------------------------------------------------
    // Thanh toán (Admin)
    // --------------------------------------------------------
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                         [PaymentController::class, 'index'])->name('index');
        Route::get('/checkout/{invoiceId}',     [PaymentController::class, 'checkout'])->name('checkout');
        Route::get('/{paymentId}',              [PaymentController::class, 'show'])->name('show');
        Route::post('/store',                   [PaymentController::class, 'store'])->name('store');
        Route::get('/{paymentId}/qr',           [PaymentController::class, 'qr'])->name('qr');
        Route::post('/{paymentId}/confirm',     [PaymentController::class, 'confirm'])->name('confirm');
        Route::post('/{paymentId}/fail',        [PaymentController::class, 'fail'])->name('fail');
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
    Route::get('/doctor-statistics', [DoctorStatisticController::class, 'index'])->name('doctor-statistics.index');
    Route::get('/revenue',           [RevenueController::class, 'index'])->name('revenue.index');

    // --------------------------------------------------------
    // Quản lý Tiêm chủng
    // --------------------------------------------------------
    Route::resource('vaccines',            VaccineController::class);
    Route::resource('vaccination-records', VaccinationRecordController::class);


    // --------------------------------------------------------
    // Bản tin bệnh viện (Admin)
    // --------------------------------------------------------
    Route::resource('news', AdminNewsController::class)
        ->parameters(['news' => 'id'])
        ->except(['show']);
    Route::patch('news/{id}/toggle',     [AdminNewsController::class, 'togglePublish'])->name('news.toggle');
    Route::post('news/{id}/send-email',  [AdminNewsController::class, 'sendEmail'])->name('news.sendEmail');

    // --------------------------------------------------------
    // Chat CSKH – Admin/Staff Routes
    // --------------------------------------------------------

    // Quản lý Thư viện Phục hồi (Admin)
    Route::resource('rehab-exercises', AdminRehabExerciseController::class)
        ->names('rehab')
        ->parameters(['rehab-exercises' => 'exercise'])
        ->except(['show']);

    // Chat Admin

    Route::prefix('chatroom')->name('chatroom.')->group(function () {
        Route::get('/',                        [ChatRoomController::class, 'index'])->name('index');
        Route::get('/list',                    [ChatRoomController::class, 'listJson'])->name('list');
        Route::get('/{roomId}/messages',       [ChatRoomController::class, 'getMessages'])->name('messages');
        Route::post('/{roomId}/send',          [ChatRoomController::class, 'sendMessage'])->name('send');
        Route::post('/{roomId}/close',         [ChatRoomController::class, 'closeRoom'])->name('close');
        Route::delete('/{roomId}',             [ChatRoomController::class, 'deleteRoom'])->name('delete');
        Route::delete('/messages/{messageId}', [ChatRoomController::class, 'deleteMessage'])->name('deleteMessage');
    });

    Route::resource('device-types', DeviceTypeController::class)
        ->parameters(['device-types' => 'device_type'])
        ->except(['show']);
    Route::resource('devices', DeviceController::class)
        ->except(['show']);

}); // Close admin block

// ============================================================

// HỆ THỐNG NHẮC NHỞ TUÂN THỰ ĐIỀU TRỊ – Admin

// HÀNG ĐỢI - Admin, Doctor, Receptionist, Pharmacist
// ============================================================
Route::middleware(['auth', 'check_queue_role:1,2,4,5'])->prefix('admin/queue')->name('admin.queue.')->group(function () {
    Route::get('/',                 [QueueController::class, 'index'])->name('index');
    Route::get('/report',           [QueueController::class, 'report'])->name('report');
    Route::get('/{scheduleId}',     [QueueController::class, 'show'])->name('show')->whereNumber('scheduleId');
    Route::get('/api/snapshot/{scheduleId}', [QueueController::class, 'apiSnapshot'])->name('api.snapshot')->whereNumber('scheduleId');
    Route::get('/api/all-snapshots', [QueueController::class, 'apiAllSnapshots'])->name('api.all-snapshots');
});


// ============================================================
// HỆ THỐNG NHẮC NHỞ TUÂN THỦ ĐIỀU TRỊ

// ============================================================


Route::middleware(['auth', 'is_admin'])->prefix('admin/treatment-reminders')->name('admin.treatment.')->group(function () {
    Route::get('/',                  [TreatmentReminderAdminController::class, 'index'])->name('index');
    Route::get('/create',            [TreatmentReminderAdminController::class, 'create'])->name('create');
    Route::post('/',                 [TreatmentReminderAdminController::class, 'store'])->name('store');
    Route::get('/{user}/show',       [TreatmentReminderAdminController::class, 'show'])->name('show');
    Route::get('/{reminder}/edit',   [TreatmentReminderAdminController::class, 'edit'])->name('edit');
    Route::put('/{reminder}',        [TreatmentReminderAdminController::class, 'update'])->name('update');
    Route::delete('/{reminder}',     [TreatmentReminderAdminController::class, 'destroy'])->name('destroy');
    Route::post('/generate/{record}',[TreatmentReminderAdminController::class, 'generateFromRecord'])->name('generate');
    Route::get('/compliance-report', [TreatmentReminderAdminController::class, 'complianceReport'])->name('compliance');


/// Admin Treatment
    Route::prefix('treatment-reminders')->name('treatment.')->group(function () {
        Route::get('/', [TreatmentReminderAdminController::class, 'index'])->name('index');
        Route::get('/create', [TreatmentReminderAdminController::class, 'create'])->name('create');
        Route::post('/', [TreatmentReminderAdminController::class, 'store'])->name('store');
        Route::get('/{user}/show', [TreatmentReminderAdminController::class, 'show'])->name('show');
        Route::get('/{reminder}/edit', [TreatmentReminderAdminController::class, 'edit'])->name('edit');
        Route::put('/{reminder}', [TreatmentReminderAdminController::class, 'update'])->name('update');
        Route::delete('/{reminder}', [TreatmentReminderAdminController::class, 'destroy'])->name('destroy');
        Route::post('/generate/{record}', [TreatmentReminderAdminController::class, 'generateFromRecord'])->name('generate');
        Route::get('/compliance-report', [TreatmentReminderAdminController::class, 'complianceReport'])->name('compliance');
    });

}); // ← đóng block admin

// ============================================================
// PATIENT – Treatment & Rehab (NGOÀI admin, chỉ cần auth)
// ============================================================

Route::middleware(['auth'])->group(function () {

    // Tuân thủ điều trị
    Route::prefix('treatment')->name('treatment.')->group(function () {
        Route::get('/', [TreatmentReminderController::class, 'index'])->name('index');
        Route::post('/confirm/{reminder}', [TreatmentReminderController::class, 'confirm'])->name('confirm')->whereNumber('reminder');
        Route::post('/instruction/toggle', [TreatmentReminderController::class, 'toggleInstruction'])->name('instruction.toggle');
        Route::get('/report', [TreatmentReminderController::class, 'report'])->name('report');
    });

    // Thư viện phục hồi chức năng
    Route::prefix('rehab-exercises')->name('rehab.')->group(function () {
        Route::get('/', [RehabExerciseController::class, 'index'])->name('index');
        Route::get('/{exercise}', [RehabExerciseController::class, 'show'])->name('show')->whereNumber('exercise');
    });


});




// ── ADVANCED PATIENT SEARCH WITH AI ────────────────────────
Route::prefix('admin')->middleware(['auth', 'role:Admin,Lễ tân,Bác sĩ'])->group(function () {
    Route::get('/patients/search', [PatientSearchController::class, 'index'])->name('admin.patients.search');
    Route::get('/patients/search/results', [PatientSearchController::class, 'search'])->name('admin.patients.search.results');
    Route::get('/patients/{id}/detail', [PatientSearchController::class, 'detail'])->name('admin.patients.detail');
    Route::post('/patients/ai-search', [PatientSearchController::class, 'aiSearch'])->name('admin.patients.ai-search');
});
// ============================================================
// HỆ THỐNG HÀNG ĐỢI KHÁM BỆNH (QUEUE SYSTEM)
// ============================================================

// Màn hình TV - không cần auth nhưng có auth route index
Route::get('/queue/display', [QueueDisplayController::class, 'index'])->name('queue.display.index');
Route::get('/queue/display/{scheduleId}', [QueueDisplayController::class, 'show'])->name('queue.display')->whereNumber('scheduleId');
Route::get('/api/queue/{scheduleId}/snapshot', [QueueDisplayController::class, 'apiSnapshot'])->name('queue.api.display')->whereNumber('scheduleId');

// Lễ tân + Admin
Route::middleware(['auth', 'check_queue_role:1,4'])->prefix('queue/manage')->name('queue.manage.')->group(function () {
    Route::get('/',                          [QueueManageController::class, 'index'])->name('index');
    Route::get('/schedule/{scheduleId}',     [QueueManageController::class, 'show'])->name('show')->whereNumber('scheduleId');
    Route::get('/checkin',                   [QueueManageController::class, 'searchPatient'])->name('checkin');
    Route::post('/checkin',                  [QueueManageController::class, 'checkin'])->name('checkin.store');
    Route::post('/ticket/{ticketId}/skip',   [QueueManageController::class, 'skip'])->name('ticket.skip')->whereNumber('ticketId');
    Route::get('/api/{scheduleId}/snapshot', [QueueManageController::class, 'apiSnapshot'])->name('api.snapshot')->whereNumber('scheduleId');
});

// Bác sĩ + Admin
Route::middleware(['auth', 'check_queue_role:1,2'])->prefix('queue/doctor')->name('queue.doctor.')->group(function () {
    Route::get('/',                                    [QueueDoctorController::class, 'index'])->name('index');
    Route::post('/schedule/{scheduleId}/call-next',    [QueueDoctorController::class, 'callNext'])->name('call.next')->whereNumber('scheduleId');
    Route::post('/ticket/{ticketId}/start',            [QueueDoctorController::class, 'startExam'])->name('start')->whereNumber('ticketId');
    Route::post('/ticket/{ticketId}/complete',         [QueueDoctorController::class, 'complete'])->name('complete')->whereNumber('ticketId');
    Route::get('/api/{scheduleId}/snapshot',           [QueueDoctorController::class, 'apiSnapshot'])->name('api.snapshot')->whereNumber('scheduleId');

});
// ============================================================
// CHẾ ĐỘ DINH DƯỠNG
// ============================================================

Route::middleware(['auth', 'role:1,2'])->prefix('admin/nutrition')->name('admin.nutrition.')->group(function () {
    // 1. Quản lý bài viết (Nutrition Articles)
    Route::get('/', [AdminNutritionController::class, 'index'])->name('index');
    Route::get('/create', [AdminNutritionController::class, 'create'])->name('create');
    Route::post('/', [AdminNutritionController::class, 'store'])->name('store');
    Route::get('/{article}/edit', [AdminNutritionController::class, 'edit'])->name('edit')->whereNumber('article');
    Route::put('/{article}', [AdminNutritionController::class, 'update'])->name('update')->whereNumber('article');
    Route::delete('/{article}', [AdminNutritionController::class, 'destroy'])->name('destroy')->whereNumber('article');

    // 2. Quản lý quy tắc gợi ý thực đơn (Disease Nutrition Rules)
    Route::prefix('rules')->name('rules.')->group(function () {
        Route::get('/', [AdminNutritionController::class, 'rulesIndex'])->name('index');
        Route::get('/create', [AdminNutritionController::class, 'rulesCreate'])->name('create');
        Route::post('/', [AdminNutritionController::class, 'rulesStore'])->name('store');
        Route::get('/{rule}/edit', [AdminNutritionController::class, 'rulesEdit'])->name('edit')->whereNumber('rule');
        Route::put('/{rule}', [AdminNutritionController::class, 'rulesUpdate'])->name('update')->whereNumber('rule');
        Route::delete('/{rule}', [AdminNutritionController::class, 'rulesDestroy'])->name('destroy')->whereNumber('rule');
    });

    // 3. Quản lý danh mục thực phẩm & calo (Foods Database)
    Route::prefix('foods')->name('foods.')->group(function () {
        Route::get('/', [AdminNutritionController::class, 'foodsIndex'])->name('index');
        Route::get('/create', [AdminNutritionController::class, 'foodsCreate'])->name('create');
        Route::post('/', [AdminNutritionController::class, 'foodsStore'])->name('store');
        Route::get('/{food}/edit', [AdminNutritionController::class, 'foodsEdit'])->name('edit')->whereNumber('food');
        Route::put('/{food}', [AdminNutritionController::class, 'foodsUpdate'])->name('update')->whereNumber('food');
        Route::delete('/{food}', [AdminNutritionController::class, 'foodsDestroy'])->name('destroy')->whereNumber('food');
    });
});

Route::middleware(['auth'])->prefix('patient/nutrition')->name('patient.nutrition.')->group(function () {
    Route::get('/', [PatientNutritionController::class, 'index'])->name('index');
    Route::post('/meal-log', [PatientNutritionController::class, 'storeMealLog'])->name('meal-log.store');
    Route::delete('/meal-log/{mealLog}', [PatientNutritionController::class, 'destroyMealLog'])->name('meal-log.destroy')->whereNumber('mealLog');
});

// ============================================================
// NHẬT KÝ SỨC KHỎE CHỦ ĐỘNG
// ============================================================

Route::middleware('auth')->group(function () {
    Route::get('/health-tracking', [HealthTrackingController::class, 'index'])->name('health-tracking.index');
    Route::post('/api/health-tracking/check-risk', [HealthTrackingController::class, 'checkRisk'])->name('health-tracking.check-risk');

    // Chỉ patient mới được tạo/sửa/xóa
    Route::middleware('can:create,App\Models\HealthTracking')->group(function () {
        Route::get('/health-tracking/create', [HealthTrackingController::class, 'create'])->name('health-tracking.create');
        Route::post('/health-tracking', [HealthTrackingController::class, 'store'])->name('health-tracking.store');
        Route::get('/health-tracking/{healthTracking}/edit', [HealthTrackingController::class, 'edit'])->name('health-tracking.edit')->whereNumber('healthTracking');
        Route::put('/health-tracking/{healthTracking}', [HealthTrackingController::class, 'update'])->name('health-tracking.update')->whereNumber('healthTracking');
        Route::delete('/health-tracking/{healthTracking}', [HealthTrackingController::class, 'destroy'])->name('health-tracking.destroy')->whereNumber('healthTracking');
    });

    Route::get('/health-tracking/{healthTracking}', [HealthTrackingController::class, 'show'])->name('health-tracking.show')->whereNumber('healthTracking');
});
