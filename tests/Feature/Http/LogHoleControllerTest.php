<?php

use DigitalDevLx\LogHole\Tests\Helpers\LogSeeder;

uses(LogSeeder::class);

beforeEach(function () {
    config()->set('log-hole.authorized_users', []);
});

it('returns 200 on dashboard index', function () {
    $this->get(route('log-hole.dashboard'))
        ->assertStatus(200);
});

it('shows empty state when no logs exist', function () {
    $this->get(route('log-hole.dashboard'))
        ->assertStatus(200)
        ->assertSee('No logs found');
});

it('displays logs in the dashboard', function () {
    $this->seedLogs(3, 'INFO');

    $this->get(route('log-hole.dashboard'))
        ->assertStatus(200)
        ->assertSee('Test log message 1');
});

it('filters logs by level', function () {
    $this->seedMixedLogs();

    $this->get(route('log-hole.dashboard', ['level' => 'error']))
        ->assertStatus(200)
        ->assertSee('Test ERROR log')
        ->assertDontSee('Test INFO log');
});

it('filters logs by search term', function () {
    $this->seedMixedLogs();

    $this->get(route('log-hole.dashboard', ['search' => 'WARNING']))
        ->assertStatus(200)
        ->assertSee('Test WARNING log');
});

it('filters logs by date range', function () {
    $this->seedLogs(5);

    $from = now()->subMinutes(10)->toDateString();
    $to = now()->toDateString();

    $this->get(route('log-hole.dashboard', ['from' => $from, 'to' => $to]))
        ->assertStatus(200);
});

it('paginates logs', function () {
    $this->seedLogs(30);

    $this->get(route('log-hole.dashboard'))
        ->assertStatus(200)
        ->assertSee('Showing');
});

it('displays stats bar with level counts', function () {
    $this->seedMixedLogs();

    $this->get(route('log-hole.dashboard'))
        ->assertStatus(200)
        ->assertSee('Total');
});
