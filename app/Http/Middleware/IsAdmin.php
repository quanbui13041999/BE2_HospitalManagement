<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $roleName = auth()->user()->role?->role_name ?? '';

        if ($roleName !== 'Admin') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}