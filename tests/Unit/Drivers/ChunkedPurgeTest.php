<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\SqliteDriver;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->driver = new SqliteDriver();
    $this->table = config('log-hole.database.table', 'logs_hole');
});

it('deletes all matching rows when chunkSize is 0 (default)', function () {
    for ($i = 0; $i < 25; $i++) {
        DB::table($this->table)->insert([
            'level' => 'INFO', 'message' => "log {$i}", 'context' => null, 'logged_at' => now(),
        ]);
    }

    expect($this->driver->purge())->toBe(25);
    expect(DB::table($this->table)->count())->toBe(0);
});

it('deletes all matching rows in chunks when chunkSize is positive', function () {
    for ($i = 0; $i < 25; $i++) {
        DB::table($this->table)->insert([
            'level' => 'INFO', 'message' => "log {$i}", 'context' => null, 'logged_at' => now(),
        ]);
    }

    expect($this->driver->purge(chunkSize: 10))->toBe(25);
    expect(DB::table($this->table)->count())->toBe(0);
});

it('respects level filter while chunking', function () {
    for ($i = 0; $i < 12; $i++) {
        DB::table($this->table)->insert([
            'level' => 'INFO', 'message' => "info {$i}", 'context' => null, 'logged_at' => now(),
        ]);
    }
    for ($i = 0; $i < 8; $i++) {
        DB::table($this->table)->insert([
            'level' => 'ERROR', 'message' => "err {$i}", 'context' => null, 'logged_at' => now(),
        ]);
    }

    $deleted = $this->driver->purge(level: LogLevel::Info, chunkSize: 5);

    expect($deleted)->toBe(12);
    expect(DB::table($this->table)->where('level', 'INFO')->count())->toBe(0);
    expect(DB::table($this->table)->where('level', 'ERROR')->count())->toBe(8);
});

it('returns 0 when nothing matches with chunking enabled', function () {
    DB::table($this->table)->insert([
        'level' => 'INFO', 'message' => 'recent', 'context' => null, 'logged_at' => now(),
    ]);

    expect($this->driver->purge(level: LogLevel::Critical, chunkSize: 100))->toBe(0);
    expect(DB::table($this->table)->count())->toBe(1);
});
