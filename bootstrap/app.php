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
            if (! $request->expectsJson()) {
                return null;
            }

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
