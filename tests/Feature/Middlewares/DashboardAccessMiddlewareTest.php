<?php

use DigitalDevLx\LogHole\Middlewares\LogHoleDashboardAccessMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

it('allows access when authorized_users is empty', function () {
    config()->set('log-hole.authorized_users', []);

    $middleware = new LogHoleDashboardAccessMiddleware();
    $request = Request::create('/log-hole', 'GET');

    $response = $middleware->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('allows access when user email is in authorized_users', function () {
    config()->set('log-hole.authorized_users', ['admin@example.com']);

    $user = new class () {
        public string $email = 'admin@example.com';
    };

    $middleware = new LogHoleDashboardAccessMiddleware();
    $request = Request::create('/log-hole', 'GET');
    $request->setUserResolver(fn () => $user);

    $response = $middleware->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('throws AuthorizationException when user email is not authorized', function () {
    config()->set('log-hole.authorized_users', ['admin@example.com']);

    $user = new class () {
        public string $email = 'notadmin@example.com';
    };

    $middleware = new LogHoleDashboardAccessMiddleware();
    $request = Request::create('/log-hole', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware->handle($request, fn ($req) => new Response('OK'));
})->throws(AuthorizationException::class);

it('throws AuthorizationException when user is null and authorized_users is configured', function () {
    config()->set('log-hole.authorized_users', ['admin@example.com']);

    $middleware = new LogHoleDashboardAccessMiddleware();
    $request = Request::create('/log-hole', 'GET');
    $request->setUserResolver(fn () => null);

    $middleware->handle($request, fn ($req) => new Response('OK'));
})->throws(AuthorizationException::class);
