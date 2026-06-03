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

        // ── Bắt ModelNotFoundException → 404 thân thiện ──────────────
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy mục dữ liệu được yêu cầu.',
                    'data'    => null,
                ], 404);
            }

            // Nếu là POST/DELETE form, redirect về danh sách với thông báo
            if (!in_array($request->method(), ['GET', 'HEAD'])) {
                return redirect()->back()
                    ->with('error', 'Mục dữ liệu không còn tồn tại hoặc đã bị xóa bởi người khác. Vui lòng tải lại trang.');
            }

            // GET request → render trang 404 tùy chỉnh
            return response()->view('errors.404', [], 404);
        });

        // ── Bắt lỗi 404 thông thường (route không khớp) ──────────────
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy trang hoặc tài nguyên được yêu cầu.',
                    'data'    => null,
                ], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // ── Bắt lỗi 403 ──────────────────────────────────────────────
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện thao tác này.',
                    'data'    => null,
                ], 403);
            }

            return response()->view('errors.403', [], 403);
        });

        // ── Bắt lỗi DB Unique constraint (trùng lặp dữ liệu) ────────
        $exceptions->render(function (
            \Illuminate\Database\QueryException $e,
            \Illuminate\Http\Request $request
        ) {
            // MySQL error code 1062 = Duplicate entry
            if ($e->getCode() == 23000) {
                $msg = 'Dữ liệu đã tồn tại trong hệ thống, vui lòng kiểm tra lại.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                        'data'    => null,
                    ], 409);
                }

                return redirect()->back()->withInput()->with('error', $msg);
            }

            return null; // Để Laravel xử lý các lỗi DB khác
        });

        // ── API: JSON rendering chung ─────────────────────────────────
        $exceptions->render(function (Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->expectsJson()) {
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
            }

            report($e);

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = $status >= 500 ? 'Đã xảy ra lỗi, vui lòng thử lại sau.' : 'Yêu cầu không hợp lệ.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
            ], $status);
        });
    })->create();
