<?php

use App\Http\Controllers\DoctorScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('schedules')->group(function () {
        // ─── Recurring Schedule ───────────────────────────────────────
        Route::post('recurring/preview', [DoctorScheduleController::class, 'recurringPreview']);
        Route::post('recurring', [DoctorScheduleController::class, 'storeRecurring']);
        Route::get('recurring/{doctorId}', [DoctorScheduleController::class, 'indexRecurring']);
        Route::delete('recurring/{scheduleId}', [DoctorScheduleController::class, 'destroyRecurring']);

        // ─── Day-Off (Block + Email) ──────────────────────────────────
        Route::post('day-off', [DoctorScheduleController::class, 'storeDayOff']);
        Route::get('day-off/{doctorId}', [DoctorScheduleController::class, 'indexDayOff']);
        Route::delete('day-off/{scheduleId}', [DoctorScheduleController::class, 'destroyDayOff']);

        // ─── Utility ──────────────────────────────────────────────────
        Route::get('doctors', [DoctorScheduleController::class, 'listDoctors']);
    });
});
