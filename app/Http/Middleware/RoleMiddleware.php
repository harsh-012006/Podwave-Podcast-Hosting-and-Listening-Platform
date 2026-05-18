<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 * Checks if the authenticated user has one of the required roles.
 *
 * Usage in routes: ->middleware('role:admin,creator')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Banned users cannot access anything except public pages
        if ($user->isBanned()) {
            auth()->logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been suspended. Reason: ' . $user->ban_reason]);
        }

        // Check role
        if (!in_array($user->role, $roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
