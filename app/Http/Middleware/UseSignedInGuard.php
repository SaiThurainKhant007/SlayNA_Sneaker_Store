<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admins, staff and customers sign in through separate guards (one per table).
 * Make whichever one is signed in the default for this request, so auth()->user(),
 * @auth and the "auth"/"guest" middleware work the same on every page.
 */
class UseSignedInGuard
{
    public const GUARDS = ['admin', 'staff', 'web'];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (self::GUARDS as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);
                break;
            }
        }

        return $next($request);
    }
}
