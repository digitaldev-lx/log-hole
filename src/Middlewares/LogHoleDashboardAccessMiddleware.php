<?php

namespace DigitalDevLx\LogHole\Middlewares;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogHoleDashboardAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authorizedUsers = config('log-hole.authorized_users');

        if (empty($authorizedUsers)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user === null) {
            throw new AuthorizationException('You must be logged in to view this dashboard.');
        }

        if (! in_array($user->email, $authorizedUsers, strict: true)) {
            Log::warning("User {$user->email} tried to access the LogHole dashboard.");
            throw new AuthorizationException('You don\'t have access to view this dashboard.');
        }

        return $next($request);
    }
}
