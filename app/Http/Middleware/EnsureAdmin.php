<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     * Only allow users with admin roles to proceed.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $adminRoles = ['super_admin', 'admin', 'manager'];

        $hasRole = false;
        if (method_exists($user, 'hasAnyRole')) {
            $hasRole = $user->hasAnyRole($adminRoles);
        }

        if (! $hasRole && ! in_array($user->role, $adminRoles, true)) {
            // not admin - redirect to staff portal or home
            return redirect()->intended(route('staff.portal.dashboard'));
        }

        return $next($request);
    }
}
