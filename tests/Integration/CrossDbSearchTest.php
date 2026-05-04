<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\DriverFactory;
use DigitalDevLx\LogHole\Tests\Integration\IntegrationTestCase;
use Illuminate\Support\Facades\DB;

uses(IntegrationTestCase::class);

/**
 * These tests run only when LOG_HOLE_INTEGRATION_DB is set to "pgsql" or
 * "mysql" — typically in CI against a real Postgres or MySQL service.
 *
 * They reproduce the bugs that were silent under SQLite: the LIKE wildcard
 * escaping must be honored even when the underlying DB does not treat
 * backslash as a string-literal escape (Postgres with standard_conforming_strings,
 * SQL Server, modern MySQL with NO_BACKSLASH_ESCAPES).
 */

beforeEach(function () {
    DB::table('logs_hole')->insert([
        ['level' => 'INFO', 'message' => '50% done',           'context' => null, 'logged_at' => now()],
        ['level' => 'INFO', 'message' => 'snake_case_var',     'context' => null, 'logged_at' => now()],
        ['level' => 'INFO', 'message' => 'tilde~here',         'context' => null, 'logged_at' => now()],
        ['level' => 'INFO', 'message' => 'no special chars',   'context' => null, 'logged_at' => now()],
        ['level' => 'INFO', 'message' => 'unrelated',          'context' => null, 'logged_at' => now()],
    ]);
});

it('treats % as a literal character in real database', function () {
    $driver = DriverFactory::make();

    $results = $driver->query(search: '%', limit: 100);

    expect($results)->toHaveCount(1);
    expect($results->first()->message)->toBe('50% done');
});

it('treats _ as a literal character in real database', function () {
    $driver = DriverFactory::make();

    $results = $driver->query(search: '_', limit: 100);

    expect($results)->toHaveCount(1);
    expect($results->first()->message)->toBe('snake_case_var');
});

it('treats ~ as a literal character (escape char does not leak into pattern)', function () {
    $driver = DriverFactory::make();

    $results = $driver->query(search: '~', limit: 100);

    expect($results)->toHaveCount(1);
    expect($results->first()->message)->toBe('tilde~here');
});

it('finds context-stored values via JSON-aware search', function () {
    DB::table('logs_hole')->insert([
        'level' => 'INFO',
        'message' => 'generic message',
        'context' => json_encode(['user_action' => 'checkout_completed']),
        'logged_at' => now(),
    ]);

    $driver = DriverFactory::make();

    $results = $driver->query(search: 'checkout_completed', limit: 100);

    expect($results->pluck('message'))->toContain('generic message');
});

it('escapes literal % when present inside JSON context value', function () {
    DB::table('logs_hole')->insert([
        'level' => 'WARNING',
        'message' => 'threshold alert',
        'context' => json_encode(['usage' => '95% full']),
        'logged_at' => now(),
    ]);

    $driver = DriverFactory::make();

    $results = $driver->query(search: '95%', limit: 100);

    expect($results->pluck('message'))->toContain('threshold alert');
    expect($results->pluck('message'))->not->toContain('no special chars');
});
