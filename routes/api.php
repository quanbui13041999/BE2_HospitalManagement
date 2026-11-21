<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentWebhookController;

/**
 * API Routes
 *
 * Note: All API v1 routes are defined in routes/web.php with the /api/v1 prefix.
 * This file is kept for Laravel's default route structure but can be used for
 * additional API routes if needed in the future.
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/cron/reminders', function () {
    $service = app(App\Services\AppointmentReminderService::class);
    $stats = $service->sendPendingReminders();
    return response()->json([
        'status' => 'success',
        'message' => 'Appointment reminders sent',
        'stats' => $stats,
    ]);
});

// Endpoint tiếp nhận webhook ngân hàng từ PayOS
Route::post('/payments/webhook', [PaymentWebhookController::class, 'handlePayOsWebhook']);
