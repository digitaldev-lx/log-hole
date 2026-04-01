<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Channels\DatabaseChannel;
use Monolog\Logger;

it('__invoke returns a Logger instance', function () {
    $channel = new DatabaseChannel();
    $logger = $channel([]);

    expect($logger)->toBeInstanceOf(Logger::class);
    expect($logger->getName())->toBe('database');
});

it('write inserts a log record into the database', function () {
    $channel = new DatabaseChannel();
    $logger = $channel([]);

    $logger->info('Test database log', ['foo' => 'bar']);

    $this->assertDatabaseHas(config('log-hole.database.table'), [
        'level' => 'INFO',
        'message' => 'Test database log',
    ]);
});

it('stores context as JSON', function () {
    $channel = new DatabaseChannel();
    $logger = $channel([]);

    $logger->error('Error occurred', ['error' => 'details']);

    $log = Illuminate\Support\Facades\DB::table(config('log-hole.database.table'))
        ->where('message', 'Error occurred')
        ->first();

    expect($log)->not->toBeNull();
    expect(json_decode($log->context, true))->toBe(['error' => 'details']);
});

it('stores null context when context is empty', function () {
    $channel = new DatabaseChannel();
    $logger = $channel([]);

    $logger->warning('Warning with no context');

    $log = Illuminate\Support\Facades\DB::table(config('log-hole.database.table'))
        ->where('message', 'Warning with no context')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->context)->toBeNull();
});
