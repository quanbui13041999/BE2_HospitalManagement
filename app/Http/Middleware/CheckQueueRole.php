<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckQueueRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = (int) $user->role_id;

        $allowedRoles = array_map('intval', $roles);

        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles)) {
            abort(403, 'Không có quyền truy cập vào chức năng này.');
        }

        return $next($request);
    }
}
