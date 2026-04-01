<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\RelationalDriver;
use DigitalDevLx\LogHole\Enums\LogLevel;
use DigitalDevLx\LogHole\Tests\Helpers\LogSeeder;
use Illuminate\Support\Facades\DB;

uses(LogSeeder::class);

beforeEach(function () {
    $this->driver = new RelationalDriver();
    $this->table = config('log-hole.database.table', 'logs_hole');
});

describe('purge all', function () {
    it('returns the number of deleted rows and empties the table', function () {
        $this->seedLogs(5, 'INFO');

        $deleted = $this->driver->purge();

        expect($deleted)->toBe(5);
        $this->assertDatabaseCount($this->table, 0);
    });

    it('returns zero when the table is already empty', function () {
        $deleted = $this->driver->purge();

        expect($deleted)->toBe(0);
    });
});

describe('purge by level', function () {
    it('deletes only rows matching the given level', function () {
        $this->seedMixedLogs();

        $deleted = $this->driver->purge(level: LogLevel::Error);

        expect($deleted)->toBe(1);
        $this->assertDatabaseMissing($this->table, ['level' => 'ERROR']);
    });

    it('leaves rows of other levels intact after purge', function () {
        $this->seedLogs(3, 'INFO');
        $this->seedLogs(2, 'ERROR');

        $this->driver->purge(level: LogLevel::Error);

        $this->assertDatabaseCount($this->table, 3);
        $this->assertDatabaseMissing($this->table, ['level' => 'ERROR']);
    });

    it('returns zero when no rows match the given level', function () {
        $this->seedLogs(3, 'INFO');

        $deleted = $this->driver->purge(level: LogLevel::Critical);

        expect($deleted)->toBe(0);
        $this->assertDatabaseCount($this->table, 3);
    });

    it('deletes all rows matching a high-severity level', function () {
        $this->seedLogs(4, 'EMERGENCY');
        $this->seedLogs(2, 'DEBUG');

        $deleted = $this->driver->purge(level: LogLevel::Emergency);

        expect($deleted)->toBe(4);
        $this->assertDatabaseCount($this->table, 2);
    });
});

describe('purge by date', function () {
    it('deletes only rows logged before the given date', function () {
        DB::table($this->table)->insert([
            ['level' => 'INFO', 'message' => 'old log', 'context' => null, 'logged_at' => now()->subDays(3)],
            ['level' => 'INFO', 'message' => 'older log', 'context' => null, 'logged_at' => now()->subDays(5)],
            ['level' => 'INFO', 'message' => 'recent log', 'context' => null, 'logged_at' => now()],
        ]);

        $deleted = $this->driver->purge(before: now()->subDays(2));

        expect($deleted)->toBe(2);
        $this->assertDatabaseHas($this->table, ['message' => 'recent log']);
        $this->assertDatabaseMissing($this->table, ['message' => 'old log']);
        $this->assertDatabaseMissing($this->table, ['message' => 'older log']);
    });

    it('returns zero when no rows exist before the given date', function () {
        DB::table($this->table)->insert([
            ['level' => 'INFO', 'message' => 'recent log A', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'recent log B', 'context' => null, 'logged_at' => now()->subMinutes(5)],
        ]);

        $deleted = $this->driver->purge(before: now()->subYear());

        expect($deleted)->toBe(0);
        $this->assertDatabaseCount($this->table, 2);
    });

    it('deletes nothing when the cutoff date is in the future', function () {
        $this->seedLogs(3, 'INFO');

        $deleted = $this->driver->purge(before: now()->addYear());

        expect($deleted)->toBe(3);
        $this->assertDatabaseCount($this->table, 0);
    });
});

describe('purge with level and date combined', function () {
    it('applies both level and date filters when both are provided', function () {
        DB::table($this->table)->insert([
            ['level' => 'ERROR', 'message' => 'old error', 'context' => null, 'logged_at' => now()->subDays(10)],
            ['level' => 'ERROR', 'message' => 'recent error', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'old info', 'context' => null, 'logged_at' => now()->subDays(10)],
            ['level' => 'INFO', 'message' => 'recent info', 'context' => null, 'logged_at' => now()],
        ]);

        $deleted = $this->driver->purge(level: LogLevel::Error, before: now()->subDays(5));

        expect($deleted)->toBe(1);
        $this->assertDatabaseMissing($this->table, ['message' => 'old error']);
        $this->assertDatabaseHas($this->table, ['message' => 'recent error']);
        $this->assertDatabaseHas($this->table, ['message' => 'old info']);
        $this->assertDatabaseHas($this->table, ['message' => 'recent info']);
    });

    it('returns zero when no rows satisfy both level and date conditions', function () {
        DB::table($this->table)->insert([
            ['level' => 'ERROR', 'message' => 'recent error', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'old info', 'context' => null, 'logged_at' => now()->subDays(10)],
        ]);

        $deleted = $this->driver->purge(level: LogLevel::Error, before: now()->subDays(5));

        expect($deleted)->toBe(0);
        $this->assertDatabaseCount($this->table, 2);
    });

    it('deletes multiple rows when multiple rows match both filters', function () {
        DB::table($this->table)->insert([
            ['level' => 'DEBUG', 'message' => 'old debug 1', 'context' => null, 'logged_at' => now()->subDays(7)],
            ['level' => 'DEBUG', 'message' => 'old debug 2', 'context' => null, 'logged_at' => now()->subDays(8)],
            ['level' => 'DEBUG', 'message' => 'recent debug', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'old info', 'context' => null, 'logged_at' => now()->subDays(7)],
        ]);

        $deleted = $this->driver->purge(level: LogLevel::Debug, before: now()->subDays(3));

        expect($deleted)->toBe(2);
        $this->assertDatabaseCount($this->table, 2);
        $this->assertDatabaseHas($this->table, ['message' => 'recent debug']);
        $this->assertDatabaseHas($this->table, ['message' => 'old info']);
    });
});
