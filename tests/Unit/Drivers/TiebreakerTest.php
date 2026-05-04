<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\SqliteDriver;
use Illuminate\Support\Facades\DB;

/**
 * Without an "ORDER BY id" tiebreaker after "ORDER BY logged_at", paginating
 * logs with identical timestamps produced unstable results: the same row could
 * appear on multiple pages or be skipped between refreshes.
 */

beforeEach(function () {
    $this->driver = new SqliteDriver();
    $this->table = config('log-hole.database.table', 'logs_hole');
});

it('orders results by logged_at and falls back to id for stable pagination', function () {
    $sameTimestamp = now()->setMicrosecond(0);

    DB::table($this->table)->insert([
        ['level' => 'INFO', 'message' => 'first',  'context' => null, 'logged_at' => $sameTimestamp],
        ['level' => 'INFO', 'message' => 'second', 'context' => null, 'logged_at' => $sameTimestamp],
        ['level' => 'INFO', 'message' => 'third',  'context' => null, 'logged_at' => $sameTimestamp],
    ]);

    $page1 = $this->driver->paginate(perPage: 2)->items();
    $page2 = $this->driver->paginate(perPage: 2);

    $messagesPage1 = array_map(fn ($row) => $row->message, $page1);

    expect($messagesPage1)->toHaveCount(2);
    expect($page2->total())->toBe(3);
    expect(count($page2->items()))->toBe(2);
});

it('emits ORDER BY clause containing both logged_at and id', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->driver->query(limit: 5);

    $log = DB::getQueryLog();
    $sql = collect($log)->pluck('query')->implode(' ');

    DB::disableQueryLog();

    expect($sql)->toContain('order by');
    expect($sql)->toContain('"logged_at"');
    expect($sql)->toContain('"id"');
});
