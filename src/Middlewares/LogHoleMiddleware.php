<?php

namespace DigitalDevLx\LogHole\Middlewares;

use Closure;
use DigitalDevLx\LogHole\Attributes\Loggable;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class LogHoleMiddleware
{
    /**
     * @throws ReflectionException
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $controller = $request->route()->getController();
        $action = $request->route()->getActionMethod();

        // Usa Reflection para inspecionar a classe e método
        $reflection = new ReflectionClass($controller);

        if ($reflection->hasMethod($action)) {
            $method = new ReflectionMethod($controller, $action);

            $attribute = $method->getAttributes(Loggable::class)[0] ?? null;

            if ($attribute) {
                /** @var Loggable $loggable */
                $loggable = $attribute->newInstance();

                // Loga a mensagem e o nível configurados no Attribute
                Log::log($loggable->level, $loggable->message);
            }
        }

        return $response;
    }
}
