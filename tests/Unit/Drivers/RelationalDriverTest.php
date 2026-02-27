<?php

use DigitalDevLx\LogHole\DataTransferObjects\LogStats;
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Drivers\RelationalDriver;
use DigitalDevLx\LogHole\Enums\LogLevel;
use DigitalDevLx\LogHole\Tests\Helpers\LogSeeder;

uses(LogSeeder::class);

beforeEach(function () {
    $this->driver = new RelationalDriver();
});

it('implements LogDriverInterface', function () {
    expect($this->driver)->toBeInstanceOf(LogDriverInterface::class);
});

it('inserts a log entry', function () {
    $this->driver->insert(LogLevel::Info, 'Test insert', ['key' => 'val'], now());

    $this->assertDatabaseHas(config('log-hole.database.table'), [
        'level' => 'INFO',
        'message' => 'Test insert',
    ]);
});

it('inserts with null context and logged_at', function () {
    $this->driver->insert(LogLevel::Debug, 'Null context test', null, null);

    $this->assertDatabaseHas(config('log-hole.database.table'), [
        'level' => 'DEBUG',
        'message' => 'Null context test',
    ]);
});

it('queries logs without filters', function () {
    $this->seedLogs(3, 'INFO');

    $logs = $this->driver->query(limit: 10);

    expect($logs)->toHaveCount(3);
});

it('queries logs with level filter', function () {
    $this->seedMixedLogs();

    $logs = $this->driver->query(level: LogLevel::Error);

    expect($logs)->toHaveCount(1);
    expect($logs->first()->level)->toBe('ERROR');
});

it('queries logs with search filter', function () {
    $this->seedMixedLogs();

    $logs = $this->driver->query(search: 'ERROR');

    expect($logs)->toHaveCount(1);
});

it('queries logs with date range filter', function () {
    $this->seedLogs(5);

    $logs = $this->driver->query(
        from: now()->subMinutes(3),
        to: now(),
    );

    expect($logs->count())->toBeGreaterThanOrEqual(1);
});

it('queries with limit', function () {
    $this->seedLogs(10);

    $logs = $this->driver->query(limit: 3);

    expect($logs)->toHaveCount(3);
});

it('paginates logs', function () {
    $this->seedLogs(15);

    $result = $this->driver->paginate(perPage: 5);

    expect($result->count())->toBe(5);
    expect($result->total())->toBe(15);
    expect($result->lastPage())->toBe(3);
});

it('purges all logs', function () {
    $this->seedLogs(5);

    $count = $this->driver->purge();

    expect($count)->toBe(5);
    $this->assertDatabaseCount(config('log-hole.database.table'), 0);
});

it('purges logs by level', function () {
    $this->seedMixedLogs();

    $count = $this->driver->purge(level: LogLevel::Error);

    expect($count)->toBe(1);
    $this->assertDatabaseMissing(config('log-hole.database.table'), ['level' => 'ERROR']);
});

it('purges logs before date', function () {
    $this->seedLogs(5);

    $count = $this->driver->purge(before: now()->subMinutes(2));

    expect($count)->toBeGreaterThanOrEqual(1);
});

it('returns stats', function () {
    $this->seedMixedLogs();

    $stats = $this->driver->stats();

    expect($stats)->toBeInstanceOf(LogStats::class);
    expect($stats->total)->toBe(8);
    expect($stats->countForLevel(LogLevel::Error))->toBe(1);
    expect($stats->countForLevel(LogLevel::Info))->toBe(1);
});

it('returns table name from config', function () {
    expect($this->driver->getTableName())->toBe('logs_hole');
});
