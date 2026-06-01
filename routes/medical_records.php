<?php

use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\HealthBackgroundController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

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
        Route::put('/{id}', [MedicalRecordController::class, 'update'])->name('update');
        Route::delete('/{id}', [MedicalRecordController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/print', [MedicalRecordController::class, 'print'])->name('print');

        // File đính kèm
        Route::post('/{id}/attachments', [MedicalRecordController::class, 'uploadAttachment'])->name('attachments.upload');
        Route::get('/{recordId}/attachments/{attachmentId}/view', [MedicalRecordController::class, 'viewAttachment'])->name('attachments.view');
        Route::delete('/{recordId}/attachments/{attachmentId}', [MedicalRecordController::class, 'deleteAttachment'])->name('attachments.destroy');

        // Kết quả xét nghiệm (chỉ Doctor/Admin)
        Route::put('/{recordId}/orders/{orderId}/result', [MedicalRecordController::class, 'updateOrderResult'])->name('orders.update-result');
        Route::delete('/{recordId}/orders/{orderId}/result', [MedicalRecordController::class, 'deleteOrderResult'])->name('orders.delete-result');
    });
// routes/web.php
Route::middleware('auth')
    ->prefix('bac-si')
    ->name('doctor.')
    ->group(function () {
        Route::get(
            '/lich-hen',
            [\App\Http\Controllers\Doctor\DoctorAppointmentController::class, 'index']
        )
            ->name('appointments.index');
    });
    // Bác sĩ xem tiền sử & tài liệu của bệnh nhân
Route::middleware('auth')->group(function () {
    Route::get('/health/patient/{patientId}', 
        [HealthBackgroundController::class, 'showPatient'])
        ->name('health.patient.show');

    Route::get('/documents/patient/{patientId}', 
        [DocumentController::class, 'indexPatient'])
        ->name('documents.patient.index');
});
