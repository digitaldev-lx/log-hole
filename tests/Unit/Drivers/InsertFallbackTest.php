<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\SqliteDriver;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->driver = new SqliteDriver();
    $this->table = config('log-hole.database.table', 'logs_hole');
});

it('falls back to current time when loggedAt is null', function () {
    $before = now();
    $this->driver->insert(LogLevel::Info, 'no timestamp', null, null);
    $after = now();

    $row = DB::table($this->table)->where('message', 'no timestamp')->first();

    expect($row)->not->toBeNull();
    expect($row->logged_at)->not->toBeNull();

    $loggedAt = strtotime((string) $row->logged_at);
    expect($loggedAt)->toBeGreaterThanOrEqual($before->copy()->subSecond()->getTimestamp());
    expect($loggedAt)->toBeLessThanOrEqual($after->copy()->addSecond()->getTimestamp());
});

it('preserves provided loggedAt when not null', function () {
    $explicit = new DateTimeImmutable('2025-01-01 12:34:56');

    $this->driver->insert(LogLevel::Warning, 'explicit timestamp', null, $explicit);

    $row = DB::table($this->table)->where('message', 'explicit timestamp')->first();

    expect($row)->not->toBeNull();
    expect((string) $row->logged_at)->toContain('2025-01-01 12:34:56');
});
