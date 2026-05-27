<?php

// Thêm vào routes/web.php:
// require __DIR__.'/health_tracking.php';

use App\Http\Controllers\HealthTrackingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/health-tracking',                        [HealthTrackingController::class, 'index'])->name('health-tracking.index');
    Route::post('/api/health-tracking/check-risk',        [HealthTrackingController::class, 'checkRisk'])->name('health-tracking.check-risk');

    // Chỉ patient mới được tạo/sửa/xóa
    Route::middleware('can:create,App\Models\HealthTracking')->group(function () {
        Route::get('/health-tracking/create',                 [HealthTrackingController::class, 'create'])->name('health-tracking.create');
        Route::post('/health-tracking',                       [HealthTrackingController::class, 'store'])->name('health-tracking.store');
        Route::get('/health-tracking/{healthTracking}/edit',  [HealthTrackingController::class, 'edit'])->name('health-tracking.edit')->whereNumber('healthTracking');
        Route::put('/health-tracking/{healthTracking}',       [HealthTrackingController::class, 'update'])->name('health-tracking.update')->whereNumber('healthTracking');
        Route::delete('/health-tracking/{healthTracking}',    [HealthTrackingController::class, 'destroy'])->name('health-tracking.destroy')->whereNumber('healthTracking');
    });

    Route::get('/health-tracking/{healthTracking}',       [HealthTrackingController::class, 'show'])->name('health-tracking.show')->whereNumber('healthTracking');
});
