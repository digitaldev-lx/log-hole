<?php

namespace DigitalDevLx\LogHole\Middlewares;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;
use ReflectionException;

class LogHoleDashboardAccessMiddleware
{

    public function handle($request, Closure $next)
    {

        $authorizedUsers = config('log-hole.dashboard_authorized_users');

        if (empty($authorizedUsers)) {
            return $next($request);
        }

        $user = $request->user();

        if (! in_array($user->email, $authorizedUsers)) {
            Log::warning('User ' . $user->email . ' tried to access the dashboard.');
            throw new AuthorizationException('You don\'t have access to view this dashboard.');
        }

        return $next($request);
    }
}
