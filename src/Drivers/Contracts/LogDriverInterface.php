<?php

namespace DigitalDevLx\LogHole\Drivers\Contracts;

use DateTimeInterface;
use DigitalDevLx\LogHole\DataTransferObjects\LogStats;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LogDriverInterface
{
    public function insert(LogLevel $level, string $message, ?array $context, ?DateTimeInterface $loggedAt): void;

    /**
     * @return Collection<int, object>
     */
    public function query(
        ?LogLevel $level = null,
        ?string $search = null,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        int $limit = 10,
        string $orderDirection = 'desc',
    ): Collection;

    public function paginate(
        ?LogLevel $level = null,
        ?string $search = null,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        int $perPage = 25,
        string $orderDirection = 'desc',
    ): LengthAwarePaginator;

    public function purge(?LogLevel $level = null, ?DateTimeInterface $before = null): int;

    public function stats(): LogStats;

    public function getTableName(): string;
}
