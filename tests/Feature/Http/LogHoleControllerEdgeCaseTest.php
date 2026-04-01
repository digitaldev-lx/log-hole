<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Tests\Helpers\LogSeeder;

uses(LogSeeder::class);

beforeEach(function () {
    config()->set('log-hole.authorized_users', []);
});

// ---------------------------------------------------------------------------
// Date parsing edge cases
// ---------------------------------------------------------------------------

it('does not crash when from param contains an invalid date format', function () {
    $this->seedLogs(3, 'INFO');

    $this->get(route('log-hole.dashboard', ['from' => 'not-a-date']))
        ->assertStatus(200);
});

it('does not crash when to param contains an invalid date format', function () {
    $this->seedLogs(3, 'INFO');

    $this->get(route('log-hole.dashboard', ['to' => 'totally-invalid-99/99/9999']))
        ->assertStatus(200);
});

it('returns 200 when both from and to are invalid date strings', function () {
    $this->seedLogs(2, 'ERROR');

    $this->get(route('log-hole.dashboard', ['from' => 'bad-date', 'to' => 'also-bad']))
        ->assertStatus(200);
});

// ---------------------------------------------------------------------------
// Level filter edge cases
// ---------------------------------------------------------------------------

it('shows all logs when level filter does not match any known level', function () {
    $this->seedMixedLogs();

    $this->get(route('log-hole.dashboard', ['level' => 'nonexistent_level']))
        ->assertStatus(200)
        ->assertSee('Test INFO log');
});

it('returns 200 with empty level string and shows all logs', function () {
    $this->seedLogs(2, 'DEBUG');

    $this->get(route('log-hole.dashboard', ['level' => '']))
        ->assertStatus(200)
        ->assertSee('Test log message 1');
});

// ---------------------------------------------------------------------------
// Search parameter edge cases
// ---------------------------------------------------------------------------

it('does not crash when search param is sent as an array', function () {
    $this->seedLogs(3, 'INFO');

    // ?search[]=foo sends an array — controller must treat it as null
    $this->get(route('log-hole.dashboard') . '?search[]=foo')
        ->assertStatus(200);
});

it('returns all logs when search string is empty', function () {
    $this->seedLogs(4, 'WARNING');

    $this->get(route('log-hole.dashboard', ['search' => '']))
        ->assertStatus(200)
        ->assertSee('Test log message 1');
});

// ---------------------------------------------------------------------------
// Combined filters
// ---------------------------------------------------------------------------

it('applies level and search filters together and returns matching logs', function () {
    $this->seedMixedLogs();

    $this->get(route('log-hole.dashboard', ['level' => 'error', 'search' => 'ERROR']))
        ->assertStatus(200)
        ->assertSee('Test ERROR log')
        ->assertDontSee('Test INFO log');
});

it('applies level, search, and date range filters together', function () {
    $this->seedMixedLogs();

    $from = now()->subDay()->toDateString();
    $to = now()->addDay()->toDateString();

    $this->get(route('log-hole.dashboard', [
        'level'  => 'warning',
        'search' => 'WARNING',
        'from'   => $from,
        'to'     => $to,
    ]))
        ->assertStatus(200)
        ->assertSee('Test WARNING log');
});

it('returns 200 with no results when level filter matches nothing in the date range', function () {
    $this->seedLogs(3, 'INFO');

    // Seeds are all recent; use a past date range that won't include them
    $from = now()->subYear()->subDay()->toDateString();
    $to = now()->subYear()->toDateString();

    $this->get(route('log-hole.dashboard', [
        'level' => 'info',
        'from'  => $from,
        'to'    => $to,
    ]))
        ->assertStatus(200)
        ->assertSee('No logs found');
});
