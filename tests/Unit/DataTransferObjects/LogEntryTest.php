<?php

use Carbon\Carbon;
use DigitalDevLx\LogHole\DataTransferObjects\LogEntry;
use DigitalDevLx\LogHole\DataTransferObjects\LogStats;
use DigitalDevLx\LogHole\Enums\LogLevel;

it('creates LogEntry from a database row', function () {
    $row = (object) [
        'id' => 1,
        'level' => 'ERROR',
        'message' => 'Something went wrong',
        'context' => json_encode(['key' => 'value']),
        'logged_at' => '2024-01-15 10:30:00',
    ];

    $entry = LogEntry::fromRow($row);

    expect($entry->id)->toBe(1);
    expect($entry->level)->toBe(LogLevel::Error);
    expect($entry->message)->toBe('Something went wrong');
    expect($entry->context)->toBe(['key' => 'value']);
    expect($entry->loggedAt)->toBeInstanceOf(Carbon::class);
});

it('handles null context', function () {
    $row = (object) [
        'id' => 2,
        'level' => 'INFO',
        'message' => 'Info message',
        'context' => null,
        'logged_at' => '2024-01-15 10:30:00',
    ];

    $entry = LogEntry::fromRow($row);

    expect($entry->context)->toBeNull();
});

it('handles null logged_at', function () {
    $row = (object) [
        'id' => 3,
        'level' => 'DEBUG',
        'message' => 'Debug message',
        'context' => null,
        'logged_at' => null,
    ];

    $entry = LogEntry::fromRow($row);

    expect($entry->loggedAt)->toBeNull();
});

it('falls back to Debug for unknown level', function () {
    $row = (object) [
        'id' => 4,
        'level' => 'UNKNOWN',
        'message' => 'Unknown level',
        'context' => null,
        'logged_at' => null,
    ];

    $entry = LogEntry::fromRow($row);

    expect($entry->level)->toBe(LogLevel::Debug);
});

it('creates LogStats with correct totals', function () {
    $stats = new LogStats(total: 100, byLevel: [
        'ERROR' => 30,
        'WARNING' => 20,
        'INFO' => 50,
    ]);

    expect($stats->total)->toBe(100);
    expect($stats->countForLevel(LogLevel::Error))->toBe(30);
    expect($stats->countForLevel(LogLevel::Warning))->toBe(20);
    expect($stats->countForLevel(LogLevel::Info))->toBe(50);
    expect($stats->countForLevel(LogLevel::Debug))->toBe(0);
});
