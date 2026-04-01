<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Attributes\Loggable;
use DigitalDevLx\LogHole\Enums\LogLevel;

it('uses info as default level', function () {
    $loggable = new Loggable();

    expect($loggable->logLevel)->toBe(LogLevel::Info);
    expect($loggable->message)->toBe('');
});

it('accepts a LogLevel enum', function () {
    $loggable = new Loggable(message: 'Test', level: LogLevel::Error);

    expect($loggable->logLevel)->toBe(LogLevel::Error);
    expect($loggable->message)->toBe('Test');
});

it('accepts a string level and converts to enum', function () {
    $loggable = new Loggable(level: 'warning');

    expect($loggable->logLevel)->toBe(LogLevel::Warning);
});

it('falls back to Info for invalid string level', function () {
    $loggable = new Loggable(level: 'invalid_level');

    expect($loggable->logLevel)->toBe(LogLevel::Info);
});

it('has readonly properties', function () {
    $loggable = new Loggable(message: 'Test', includeRequest: true, channel: 'database');

    expect($loggable->message)->toBe('Test');
    expect($loggable->includeRequest)->toBeTrue();
    expect($loggable->channel)->toBe('database');
});

it('defaults includeRequest to false and channel to null', function () {
    $loggable = new Loggable();

    expect($loggable->includeRequest)->toBeFalse();
    expect($loggable->channel)->toBeNull();
});
