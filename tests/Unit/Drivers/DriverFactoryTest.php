<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Drivers\DriverFactory;
use DigitalDevLx\LogHole\Drivers\SqliteDriver;

it('creates a driver via factory', function () {
    $driver = DriverFactory::make();

    expect($driver)->toBeInstanceOf(LogDriverInterface::class);
});

it('returns SqliteDriver for sqlite connection', function () {
    $driver = DriverFactory::make('testing');

    expect($driver)->toBeInstanceOf(SqliteDriver::class);
});
