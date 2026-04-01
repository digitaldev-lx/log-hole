<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Middlewares;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogHoleDashboardAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var array<int, string> $authorizedUsers */
        $authorizedUsers = config('log-hole.authorized_users');

        if (empty($authorizedUsers)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user === null) {
            throw new AuthorizationException('You must be logged in to view this dashboard.');
        }

        if (! Gate::forUser($user)->check('viewLogHole')) {
            $email = (string) data_get($user, 'email');
            Log::warning("User {$email} tried to access the LogHole dashboard.");
            throw new AuthorizationException('You don\'t have access to view this dashboard.');
        }

        return $next($request);
    }
}
