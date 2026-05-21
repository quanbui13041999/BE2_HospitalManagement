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

        $userRole = (string) Auth::user()->role_id;
        if (!in_array($userRole, $roles)) {
            abort(403, 'Không có quyền truy cập.');
        }

        return $next($request);
    }
}
