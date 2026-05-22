<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!Auth::check()) return redirect('/login');

        $userRoleId = (string) Auth::user()->role_id;
        $userRoleName = Auth::user()->role?->role_name ?? '';

        $allowed = false;
        foreach ($roles as $role) {
            if ($userRoleId === $role || strcasecmp($userRoleName, $role) === 0) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            abort(403, 'Không có quyền truy cập.');
        }

        return $next($request);
    }
}
