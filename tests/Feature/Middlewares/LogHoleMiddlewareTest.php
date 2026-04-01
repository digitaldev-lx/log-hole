<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Middlewares\LogHoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;

it('does nothing for routes without Loggable attribute', function () {
    $middleware = new LogHoleMiddleware();

    $controller = new class () {
        public function index(): void
        {
        }
    };

    $route = Mockery::mock(Route::class);
    $route->shouldReceive('getAction')->with('uses')->andReturn(get_class($controller) . '@index');
    $route->shouldReceive('getController')->andReturn($controller);
    $route->shouldReceive('getActionMethod')->andReturn('index');

    $request = Request::create('/test', 'GET');
    $request->setRouteResolver(fn () => $route);

    $response = $middleware->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('skips closure routes gracefully', function () {
    $middleware = new LogHoleMiddleware();

    $route = Mockery::mock(Route::class);
    $route->shouldReceive('getAction')->with('uses')->andReturn(function () {
        return 'closure';
    });

    $request = Request::create('/test', 'GET');
    $request->setRouteResolver(fn () => $route);

    $response = $middleware->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('skips when route is null', function () {
    $middleware = new LogHoleMiddleware();

    $request = Request::create('/test', 'GET');
    $request->setRouteResolver(fn () => null);

    $response = $middleware->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});
