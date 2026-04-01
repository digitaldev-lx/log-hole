<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\SqliteDriver;
use DigitalDevLx\LogHole\Tests\Helpers\LogSeeder;
use Illuminate\Support\Facades\DB;

uses(LogSeeder::class);

beforeEach(function () {
    $this->driver = new SqliteDriver();
    $this->table = config('log-hole.database.table', 'logs_hole');
});

describe('LIKE wildcard escaping', function () {
    it('searches only for literal percent sign and does not match all rows', function () {
        DB::table($this->table)->insert([
            ['level' => 'INFO', 'message' => '50% done', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'no special chars here', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'another plain message', 'context' => null, 'logged_at' => now()],
        ]);

        $results = $this->driver->query(search: '%', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->message)->toBe('50% done');
    });

    it('searches only for literal underscore and does not match all single characters', function () {
        DB::table($this->table)->insert([
            ['level' => 'INFO', 'message' => 'snake_case_variable', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'no underscore here', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'also plain', 'context' => null, 'logged_at' => now()],
        ]);

        $results = $this->driver->query(search: '_', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->message)->toBe('snake_case_variable');
    });

    it('searches only for literal backslash', function () {
        DB::table($this->table)->insert([
            ['level' => 'INFO', 'message' => 'path\\to\\file', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'no backslash here', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'another clean message', 'context' => null, 'logged_at' => now()],
        ]);

        $results = $this->driver->query(search: '\\', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->message)->toBe('path\\to\\file');
    });

    it('does not treat percent in search as SQL wildcard matching unrelated rows', function () {
        DB::table($this->table)->insert([
            ['level' => 'INFO', 'message' => 'disk usage at 80% capacity', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'unrelated log entry', 'context' => null, 'logged_at' => now()],
            ['level' => 'ERROR', 'message' => 'critical failure', 'context' => null, 'logged_at' => now()],
        ]);

        $results = $this->driver->query(search: '80%', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->message)->toBe('disk usage at 80% capacity');
    });
});

describe('normal text search', function () {
    it('finds messages containing the search term', function () {
        $this->seedMixedLogs();

        $results = $this->driver->query(search: 'ERROR', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->message)->toBe('Test ERROR log');
    });

    it('returns multiple matching rows when search term appears in several messages', function () {
        DB::table($this->table)->insert([
            ['level' => 'INFO', 'message' => 'user login successful', 'context' => null, 'logged_at' => now()],
            ['level' => 'ERROR', 'message' => 'user login failed', 'context' => null, 'logged_at' => now()],
            ['level' => 'DEBUG', 'message' => 'unrelated entry', 'context' => null, 'logged_at' => now()],
        ]);

        $results = $this->driver->query(search: 'user login', limit: 100);

        expect($results)->toHaveCount(2);
    });

    it('is case-insensitive for SQLite LIKE comparisons', function () {
        DB::table($this->table)->insert([
            ['level' => 'INFO', 'message' => 'Payment Processed', 'context' => null, 'logged_at' => now()],
            ['level' => 'INFO', 'message' => 'unrelated', 'context' => null, 'logged_at' => now()],
        ]);

        $results = $this->driver->query(search: 'payment', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->message)->toBe('Payment Processed');
    });
});

describe('context column search', function () {
    it('finds records matching search term in context column', function () {
        DB::table($this->table)->insert([
            [
                'level' => 'INFO',
                'message' => 'generic message',
                'context' => json_encode(['user_id' => 42, 'action' => 'checkout']),
                'logged_at' => now(),
            ],
            [
                'level' => 'INFO',
                'message' => 'another generic message',
                'context' => json_encode(['user_id' => 7, 'action' => 'login']),
                'logged_at' => now(),
            ],
        ]);

        $results = $this->driver->query(search: 'checkout', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->message)->toBe('generic message');
    });

    it('finds records when search term matches both message and context', function () {
        DB::table($this->table)->insert([
            [
                'level' => 'ERROR',
                'message' => 'payment failed',
                'context' => json_encode(['reason' => 'payment gateway timeout']),
                'logged_at' => now(),
            ],
            [
                'level' => 'INFO',
                'message' => 'no match here',
                'context' => json_encode(['data' => 'irrelevant']),
                'logged_at' => now(),
            ],
        ]);

        $results = $this->driver->query(search: 'payment', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->level)->toBe('ERROR');
    });

    it('searches context with literal percent sign and does not widen the match', function () {
        DB::table($this->table)->insert([
            [
                'level' => 'WARNING',
                'message' => 'threshold alert',
                'context' => json_encode(['usage' => '95% full']),
                'logged_at' => now(),
            ],
            [
                'level' => 'INFO',
                'message' => 'normal operation',
                'context' => json_encode(['status' => 'ok']),
                'logged_at' => now(),
            ],
        ]);

        $results = $this->driver->query(search: '95%', limit: 100);

        expect($results)->toHaveCount(1);
        expect($results->first()->message)->toBe('threshold alert');
    });
});

describe('empty search', function () {
    it('returns all records when search is an empty string', function () {
        $this->seedLogs(4, 'INFO');

        $results = $this->driver->query(search: '', limit: 100);

        expect($results)->toHaveCount(4);
    });

    it('returns all records when search is null', function () {
        $this->seedLogs(3, 'DEBUG');

        $results = $this->driver->query(search: null, limit: 100);

        expect($results)->toHaveCount(3);
    });
});
