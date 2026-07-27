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
        if (!in_array($user->role, ['superadmin', 'admin', 'editor', 'moderator'])) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
