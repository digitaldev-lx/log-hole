<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\SqliteDriver;
use DigitalDevLx\LogHole\Tests\Helpers\LogSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(LogSeeder::class);

beforeEach(function () {
    Cache::flush();
    $this->driver = new SqliteDriver();
    $this->table = config('log-hole.database.table', 'logs_hole');
});

afterEach(function () {
    config()->set('log-hole.stats_cache_ttl', 0);
});

it('does not cache stats when stats_cache_ttl is 0', function () {
    config()->set('log-hole.stats_cache_ttl', 0);

    $this->seedLogs(2, 'INFO');
    $first = $this->driver->stats();
    expect($first->total)->toBe(2);

    $this->seedLogs(3, 'ERROR');
    $second = $this->driver->stats();

    expect($second->total)->toBe(5);
});

it('caches stats when stats_cache_ttl is positive', function () {
    config()->set('log-hole.stats_cache_ttl', 60);

    $this->seedLogs(2, 'INFO');
    $first = $this->driver->stats();
    expect($first->total)->toBe(2);

    DB::table($this->table)->insert([
        'level' => 'ERROR', 'message' => 'after cache', 'context' => null, 'logged_at' => now(),
    ]);

    // The cached value should be returned, not the fresh count.
    $second = $this->driver->stats();
    expect($second->total)->toBe(2);
});

it('refreshes stats after cache is flushed', function () {
    config()->set('log-hole.stats_cache_ttl', 60);

    $this->seedLogs(2, 'INFO');
    $this->driver->stats();

    DB::table($this->table)->insert([
        'level' => 'ERROR', 'message' => 'after flush', 'context' => null, 'logged_at' => now(),
    ]);

    Cache::flush();

    expect($this->driver->stats()->total)->toBe(3);
});
