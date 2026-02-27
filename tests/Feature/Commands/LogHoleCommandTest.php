<?php

use DigitalDevLx\LogHole\Tests\Helpers\LogSeeder;

uses(LogSeeder::class);

it('displays logs with default options', function () {
    $this->seedLogs(3, 'INFO');

    $this->artisan('log-hole:tail')
        ->assertSuccessful();
});

it('displays warning when no logs found', function () {
    $this->artisan('log-hole:tail')
        ->assertSuccessful()
        ->expectsOutput('No logs found matching your criteria.');
});

it('filters by error level', function () {
    $this->seedMixedLogs();

    $this->artisan('log-hole:tail --error')
        ->assertSuccessful();
});

it('filters by alert level', function () {
    $this->seedMixedLogs();

    $this->artisan('log-hole:tail --alert')
        ->assertSuccessful();
});

it('respects --take option', function () {
    $this->seedLogs(10);

    $this->artisan('log-hole:tail --take=3')
        ->assertSuccessful();
});

it('filters by date range', function () {
    $this->seedLogs(5);

    $from = now()->subDay()->toDateString();
    $to = now()->toDateString();

    $this->artisan("log-hole:tail --from={$from} --to={$to}")
        ->assertSuccessful();
});

it('asks for confirmation before purge', function () {
    $this->seedLogs(3);

    $this->artisan('log-hole:tail --purge')
        ->expectsConfirmation('Are you sure you want to purge all logs?', 'yes')
        ->assertSuccessful();

    $this->assertDatabaseCount(config('log-hole.database.table'), 0);
});

it('cancels purge when user says no', function () {
    $this->seedLogs(3);

    $this->artisan('log-hole:tail --purge')
        ->expectsConfirmation('Are you sure you want to purge all logs?', 'no')
        ->assertSuccessful()
        ->expectsOutput('Purge cancelled.');

    $this->assertDatabaseCount(config('log-hole.database.table'), 3);
});
