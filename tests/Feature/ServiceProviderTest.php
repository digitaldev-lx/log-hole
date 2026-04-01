<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use Illuminate\Support\Facades\Gate;

it('registers LogDriverInterface as singleton', function () {
    $driver = app(LogDriverInterface::class);

    expect($driver)->toBeInstanceOf(LogDriverInterface::class);
    expect(app(LogDriverInterface::class))->toBe($driver);
});

it('defines viewLogHole gate', function () {
    expect(Gate::has('viewLogHole'))->toBeTrue();
});

it('viewLogHole gate returns true when authorized_users is empty', function () {
    config()->set('log-hole.authorized_users', []);

    $result = Gate::forUser(null)->allows('viewLogHole');

    expect($result)->toBeTrue();
});

it('registers the log-hole:tail command', function () {
    $this->artisan('log-hole:tail')
        ->assertSuccessful();
});

it('registers views with log-hole namespace', function () {
    $finder = app('view')->getFinder();
    $hints = $finder->getHints();

    expect($hints)->toHaveKey('log-hole');
});

it('publishes config file', function () {
    $configPath = config_path('log-hole.php');

    // The config should be loadable via the service provider
    expect(config('log-hole.database.table'))->toBe('logs_hole');
});
