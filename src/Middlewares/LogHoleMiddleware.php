<?php

namespace DigitalDevLx\LogHole\Middlewares;

use Closure;
use DigitalDevLx\LogHole\Attributes\Loggable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionMethod;

class LogHoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $route = $request->route();

        if (! $route instanceof Route) {
            return $response;
        }

        $uses = $route->getAction('uses');

        // Skip closure routes (no controller)
        if (! is_string($uses) || ! str_contains($uses, '@')) {
            return $response;
        }

        $controller = $route->getController();
        $action = $route->getActionMethod();

        $reflection = new ReflectionClass($controller);

        // Check method-level attribute first
        $loggable = null;

        if ($reflection->hasMethod($action)) {
            $method = new ReflectionMethod($controller, $action);
            $attribute = $method->getAttributes(Loggable::class)[0] ?? null;

            if ($attribute !== null) {
                $loggable = $attribute->newInstance();
            }
        }

        // Fall back to class-level attribute
        if ($loggable === null) {
            $classAttribute = $reflection->getAttributes(Loggable::class)[0] ?? null;

            if ($classAttribute !== null) {
                $loggable = $classAttribute->newInstance();
            }
        }

        if ($loggable instanceof Loggable) {
            $context = [];

            if ($loggable->includeRequest) {
                $context = [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                ];
            }

            $logger = $loggable->channel !== null
                ? Log::channel($loggable->channel)
                : Log::getFacadeRoot();

            $logger->log(
                strtolower($loggable->logLevel->value),
                $loggable->message ?: "{$action} was called",
                $context,
            );
        }

        return $response;
    }
}
