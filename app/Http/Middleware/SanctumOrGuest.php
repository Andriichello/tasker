<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Guard;
use Throwable;

/**
 * Tries to authenticate a user by the bearer token,
 * without failing if no token or an invalid token is provided.
 */
class SanctumOrGuest
{
    /**
     * Tries to authenticate a user by the bearer token,
     * without failing if not token or an invalid token is provided.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $guard = Auth::guard('sanctum');
            $user = $guard->authenticate();

            if ($user) {
                Auth::setUser($user);
            }
        } catch (Throwable) {
            //
        }

        return $next($request);
    }
}
