<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/admin/login');
        }

        $user = Auth::user();
        $role = strtolower(trim($user->role ?? ''));
        if (!in_array($role, ['superadmin', 'admin', 'editor', 'moderator'])) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
