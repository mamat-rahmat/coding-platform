<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->is_approved && ! Auth::user()->is_admin) {
            $allowed = [
                'pending-approval',
                'login',
                'logout',
                'register',
                'register.store',
                'home',
                'password.request',
                'password.email',
                'password.reset',
                'password.update',
            ];

            if ($request->routeIs(...$allowed)) {
                return $next($request);
            }

            return redirect()->route('pending-approval');
        }

        return $next($request);
    }
}
