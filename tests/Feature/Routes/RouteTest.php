<?php

declare(strict_types=1);

it('registers the dashboard route', function () {
    $route = app('router')->getRoutes()->getByName('log-hole.dashboard');

    expect($route)->not->toBeNull();
    expect($route->uri())->toBe('log-hole');
});

it('dashboard route responds to GET', function () {
    config()->set('log-hole.authorized_users', []);

    $this->get('/log-hole')
        ->assertStatus(200);
});
