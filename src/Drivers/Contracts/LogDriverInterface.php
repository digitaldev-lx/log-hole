<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Drivers\Contracts;

use DateTimeInterface;
use DigitalDevLx\LogHole\DataTransferObjects\LogStats;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use stdClass;

interface LogDriverInterface
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function insert(LogLevel $level, string $message, ?array $context, ?DateTimeInterface $loggedAt): void;

    /**
     * @return Collection<int, stdClass>
     */
    public function query(
        ?LogLevel $level = null,
        ?string $search = null,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        int $limit = 10,
        string $orderDirection = 'desc',
    ): Collection;

    /**
     * @return LengthAwarePaginator<int, stdClass>
     */
    public function paginate(
        ?LogLevel $level = null,
        ?string $search = null,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        int $perPage = 25,
        string $orderDirection = 'desc',
    ): LengthAwarePaginator;

    /**
     * Delete log rows matching the given filters.
     *
     * @param  int  $chunkSize  When > 0, deletes in batches of this size.
     *                          When 0 (default), deletes in a single statement.
     *                          Use chunking on tables with millions of rows to
     *                          reduce lock contention and binlog size.
     */
    public function purge(?LogLevel $level = null, ?DateTimeInterface $before = null, int $chunkSize = 0): int;

    public function stats(): LogStats;

    public function getTableName(): string;
}
