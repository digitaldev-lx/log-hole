<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Tests\Helpers\LogSeeder;

uses(LogSeeder::class);

// ---------------------------------------------------------------------------
// Level flags not covered by the existing test file
// ---------------------------------------------------------------------------

it('filters by --emergency flag', function () {
    $this->seedMixedLogs();

    $this->artisan('log-hole:tail --emergency')
        ->assertSuccessful()
        ->expectsOutputToContain('EMERGENCY');
});

it('filters by --critical flag', function () {
    $this->seedMixedLogs();

    $this->artisan('log-hole:tail --critical')
        ->assertSuccessful()
        ->expectsOutputToContain('CRITICAL');
});

it('filters by --warning flag', function () {
    $this->seedMixedLogs();

    $this->artisan('log-hole:tail --warning')
        ->assertSuccessful()
        ->expectsOutputToContain('WARNING');
});

it('filters by --notice flag', function () {
    $this->seedMixedLogs();

    $this->artisan('log-hole:tail --notice')
        ->assertSuccessful()
        ->expectsOutputToContain('NOTICE');
});

it('filters by --info flag', function () {
    $this->seedMixedLogs();

    $this->artisan('log-hole:tail --info')
        ->assertSuccessful()
        ->expectsOutputToContain('INFO');
});

it('filters by --debug flag', function () {
    $this->seedMixedLogs();

    $this->artisan('log-hole:tail --debug')
        ->assertSuccessful()
        ->expectsOutputToContain('DEBUG');
});

// ---------------------------------------------------------------------------
// --take clamping
// ---------------------------------------------------------------------------

it('clamps --take=0 to a minimum of 1 and still runs successfully', function () {
    $this->seedLogs(5, 'INFO');

    $this->artisan('log-hole:tail --take=0')
        ->assertSuccessful()
        ->expectsOutputToContain('limit: 1');
});

it('clamps --take=-5 to a minimum of 1', function () {
    $this->seedLogs(5, 'INFO');

    $this->artisan('log-hole:tail --take=-5')
        ->assertSuccessful()
        ->expectsOutputToContain('limit: 1');
});

it('clamps --take=9999 to a maximum of 1000', function () {
    $this->seedLogs(3, 'INFO');

    $this->artisan('log-hole:tail --take=9999')
        ->assertSuccessful()
        ->expectsOutputToContain('limit: 1000');
});

it('accepts a valid --take value within bounds unchanged', function () {
    $this->seedLogs(5, 'INFO');

    $this->artisan('log-hole:tail --take=5')
        ->assertSuccessful()
        ->expectsOutputToContain('limit: 5');
});

// ---------------------------------------------------------------------------
// Invalid date handling
// ---------------------------------------------------------------------------

it('shows a warning and continues when --from has an invalid date format', function () {
    $this->seedLogs(3, 'INFO');

    $this->artisan('log-hole:tail --from=not-a-real-date')
        ->assertSuccessful()
        ->expectsOutputToContain('Invalid date format: not-a-real-date');
});

it('shows a warning and continues when --to has an invalid date format', function () {
    $this->seedLogs(3, 'INFO');

    $this->artisan('log-hole:tail --to=99/99/9999')
        ->assertSuccessful()
        ->expectsOutputToContain('Invalid date format: 99/99/9999');
});

it('still returns logs after ignoring an invalid --from date', function () {
    $this->seedLogs(3, 'INFO');

    $this->artisan('log-hole:tail --from=bad-date')
        ->assertSuccessful()
        ->expectsOutputToContain('Test log message');
});

// ---------------------------------------------------------------------------
// Combined level + date filters
// ---------------------------------------------------------------------------

it('combines level flag with a valid date range and returns only matching logs', function () {
    $this->seedMixedLogs();

    $from = now()->subDay()->toDateString();
    $to = now()->addDay()->toDateString();

    $this->artisan("log-hole:tail --error --from={$from} --to={$to}")
        ->assertSuccessful()
        ->expectsOutputToContain('ERROR');
});

it('shows no logs message when level flag matches nothing in the given date range', function () {
    $this->seedLogs(3, 'INFO');

    $from = now()->subYear()->subDay()->toDateString();
    $to = now()->subYear()->toDateString();

    $this->artisan("log-hole:tail --info --from={$from} --to={$to}")
        ->assertSuccessful()
        ->expectsOutput('No logs found matching your criteria.');
});
