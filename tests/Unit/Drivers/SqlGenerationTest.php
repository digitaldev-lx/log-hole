<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Drivers\MySqlDriver;
use DigitalDevLx\LogHole\Drivers\PostgreSqlDriver;
use DigitalDevLx\LogHole\Drivers\RelationalDriver;
use DigitalDevLx\LogHole\Drivers\SqliteDriver;
use DigitalDevLx\LogHole\Drivers\SqlServerDriver;
use Illuminate\Support\Facades\DB;

/**
 * Validates that every driver emits the ESCAPE clause for LIKE/ILIKE searches.
 *
 * The bug these tests guard against: prior to v4, PostgreSqlDriver and
 * SqlServerDriver escaped wildcards in the bound value but emitted plain LIKE
 * without an ESCAPE clause. Since neither DB treats backslash as an escape
 * character by default, searches for "%" or "_" matched everything.
 *
 * We use DB::pretend() to capture the SQL each driver emits without executing
 * it, which lets us inspect Postgres/SqlServer-specific SQL while the test
 * suite runs against SQLite.
 */

/**
 * @return list<string>
 */
function captureSqlForSearch(callable $build): array
{
    $queries = DB::pretend(function () use ($build) {
        $build();
    });

    return array_map(fn (array $entry): string => (string) $entry['query'], $queries);
}

it('SqliteDriver emits ESCAPE clause in LIKE queries', function () {
    $sql = implode(' ', captureSqlForSearch(
        fn () => (new SqliteDriver())->query(search: 'anything', limit: 1),
    ));

    expect($sql)->toContain('ESCAPE');
});

it('RelationalDriver fallback emits ESCAPE clause in LIKE queries', function () {
    $sql = implode(' ', captureSqlForSearch(
        fn () => (new RelationalDriver())->query(search: 'anything', limit: 1),
    ));

    expect($sql)->toContain('ESCAPE');
});

it('MySqlDriver builds SQL with ESCAPE on both message and context', function () {
    $sql = implode(' ', captureSqlForSearch(
        fn () => (new MySqlDriver())->query(search: 'anything', limit: 1),
    ));

    expect($sql)->toContain('ESCAPE');
    expect($sql)->toContain('CAST(context AS CHAR) LIKE');
});

it('PostgreSqlDriver builds SQL with ILIKE and ESCAPE clause', function () {
    $sql = implode(' ', captureSqlForSearch(
        fn () => (new PostgreSqlDriver())->query(search: 'anything', limit: 1),
    ));

    expect($sql)->toContain('ILIKE');
    expect($sql)->toContain('ESCAPE');
    expect($sql)->toContain('context::text');
});

it('SqlServerDriver builds SQL with ESCAPE clause and NVARCHAR cast', function () {
    $sql = implode(' ', captureSqlForSearch(
        fn () => (new SqlServerDriver())->query(search: 'anything', limit: 1),
    ));

    expect($sql)->toContain('ESCAPE');
    expect($sql)->toContain('NVARCHAR(MAX)');
});

it('passes ~ as the bound escape character so DB literal handling is irrelevant', function () {
    $queries = DB::pretend(function () {
        (new SqliteDriver())->query(search: 'anything', limit: 1);
    });

    $bindings = collect($queries)->flatMap(fn (array $entry): array => $entry['bindings'])->all();

    expect($bindings)->toContain('~');
});
