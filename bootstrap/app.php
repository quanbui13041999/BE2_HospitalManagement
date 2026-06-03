<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'check_queue_role' => \App\Http\Middleware\CheckQueueRole::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, \Illuminate\Http\Request $request) {
            $modelNotFoundException = null;
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $modelNotFoundException = $e;
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException && $e->getPrevious() instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $modelNotFoundException = $e->getPrevious();
            }

            if ($modelNotFoundException) {
                $modelClass = $modelNotFoundException->getModel();
                
                $modelMap = [
                    \App\Models\HospitalNews::class => [
                        'name' => 'Bản tin bệnh viện',
                        'admin' => 'admin.news.index',
                        'user' => 'news.index',
                    ],
                    \App\Models\Appointment::class => [
                        'name' => 'Lịch hẹn khám',
                        'admin' => 'admin.dashboard',
                        'user' => 'user.appointments.index',
                    ],
                    \App\Models\DoctorSchedule::class => [
                        'name' => 'Lịch khám của bác sĩ',
                        'admin' => 'admin.rooms.weekly',
                        'user' => 'doctor.schedule',
                    ],
                    \App\Models\User::class => [
                        'name' => 'Người dùng hoặc bệnh nhân',
                        'admin' => 'admin.patients.search',
                        'user' => 'Home.trangchu',
                    ],
                    \App\Models\ChatRoom::class => [
                        'name' => 'Phòng trò chuyện',
                        'admin' => 'admin.chatroom.index',
                        'user' => 'chat.index',
                    ],
                    \App\Models\ChatMessage::class => [
                        'name' => 'Tin nhắn',
                        'admin' => 'admin.chatroom.index',
                        'user' => 'chat.index',
                    ],
                    \App\Models\TreatmentReminder::class => [
                        'name' => 'Lịch nhắc điều trị',
                        'admin' => 'admin.treatment.index',
                        'user' => 'treatment.index',
                    ],
                    \App\Models\MedicalRecord::class => [
                        'name' => 'Hồ sơ bệnh án',
                        'admin' => 'medical-records.index',
                        'user' => 'medical-records.index',
                    ],
                    \App\Models\Payment::class => [
                        'name' => 'Giao dịch thanh toán',
                        'admin' => 'admin.payments.index',
                        'user' => 'user.payments.history',
                    ],
                    \App\Models\Vaccine::class => [
                        'name' => 'Thông tin vắc-xin',
                        'admin' => 'admin.vaccines.index',
                        'user' => 'Home.trangchu',
                    ],
                    \App\Models\VaccinationRecord::class => [
                        'name' => 'Hồ sơ tiêm chủng',
                        'admin' => 'admin.vaccination-records.index',
                        'user' => 'Home.trangchu',
                    ],
                    \App\Models\RehabExercise::class => [
                        'name' => 'Bài tập phục hồi chức năng',
                        'admin' => 'admin.rehab.index',
                        'user' => 'rehab.index',
                    ],
                    \App\Models\Notification::class => [
                        'name' => 'Thông báo',
                        'admin' => 'admin.dashboard',
                        'user' => 'notifications.index',
                    ],
                    \App\Models\Room::class => [
                        'name' => 'Phòng khám/phòng bệnh',
                        'admin' => 'admin.rooms.index',
                        'user' => 'Home.trangchu',
                    ],
                    \App\Models\Service::class => [
                        'name' => 'Dịch vụ y tế',
                        'admin' => 'admin.services.index',
                        'user' => 'user.services.index',
                    ],
                    \App\Models\BhytCard::class => [
                        'name' => 'Thẻ bảo hiểm y tế',
                        'admin' => 'admin.bhyt.index',
                        'user' => 'user.insurance',
                    ],
                ];

                $modelName = 'Dữ liệu';
                $redirectRoute = null;

                $isAdmin = str_contains($request->url(), '/admin/') || str_contains($request->url(), '/admin');

                if (isset($modelMap[$modelClass])) {
                    $modelInfo = $modelMap[$modelClass];
                    $modelName = $modelInfo['name'];
                    $redirectRoute = $isAdmin ? ($modelInfo['admin'] ?? null) : ($modelInfo['user'] ?? null);
                } else {
                    $baseName = class_basename($modelClass);
                    if ($baseName) {
                        $modelName = 'Dữ liệu ' . $baseName;
                    }
                }

                $message = "{$modelName} không tồn tại hoặc đã bị xóa. Vui lòng kiểm tra hoặc thử lại.";

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'data' => null,
                    ], 404);
                }

                if ($redirectRoute && \Illuminate\Support\Facades\Route::has($redirectRoute)) {
                    return redirect()->route($redirectRoute)->with('warning', $message);
                }

                if (url()->previous() && url()->previous() !== url()->current()) {
                    return redirect()->back()->with('warning', $message);
                }

                $fallbackUrl = $isAdmin ? '/admin' : '/';
                return redirect()->to($fallbackUrl)->with('warning', $message);
            }

            if (! $request->expectsJson()) {
                // 1. Lỗi sai đường dẫn / không tìm thấy trang (404)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $message = 'Đường dẫn không tồn tại hoặc yêu cầu không hợp lệ.';
                    $isAdmin = str_contains($request->url(), '/admin/') || str_contains($request->url(), '/admin');
                    $fallbackUrl = $isAdmin ? '/admin' : '/';
                    
                    if (url()->previous() && url()->previous() !== url()->current()) {
                        return redirect()->back()->with('warning', $message);
                    }
                    return redirect()->to($fallbackUrl)->with('warning', $message);
                }

                // 2. Lỗi không có quyền truy cập (403)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException || $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    $message = 'Bạn không có quyền truy cập vào liên kết này.';
                    if (url()->previous() && url()->previous() !== url()->current()) {
                        return redirect()->back()->with('error', $message);
                    }
                    return redirect()->to('/')->with('error', $message);
                }

                // 3. Lỗi hết hạn phiên làm việc/CSRF Token (419)
                if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                    $message = 'Phiên làm việc đã hết hạn do lâu không tương tác. Vui lòng tải lại trang và thử lại.';
                    return redirect()->back()->with('warning', $message)->withInput();
                }

                // 4. Lỗi sai phương thức yêu cầu (Method Not Allowed)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    $message = 'Phương thức yêu cầu không hợp lệ cho đường dẫn này.';
                    return redirect()->back()->with('warning', $message);
                }

                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu nhập không hợp lệ.',
                    'data' => [
                        'errors' => $e->errors(),
                    ],
                ], 422);
            } /* fixed: input sai phai tra 422, khong bi handler chung bien thanh 500 */

            report($e); /* fixed: API log loi noi bo va tra JSON chung */

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = $status >= 500 ? 'Đã xảy ra lỗi, vui lòng thử lại sau.' : 'Yêu cầu không hợp lệ.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
            ], $status);
        });
    })->create();
