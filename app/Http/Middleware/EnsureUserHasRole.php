<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict a route to one or more account roles (customer/staff/admin).
 * Equivalent to require_role() in the original PHP prototype's includes/auth.php.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'You are not authorized to view this page.');
        }

        if ($user->status !== 'active') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been disabled.']);
        }

        return $next($request);
    }
}
