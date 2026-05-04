<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Drivers;

use DateTimeInterface;
use DateTimeImmutable;
use DigitalDevLx\LogHole\DataTransferObjects\LogStats;
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;

class RelationalDriver implements LogDriverInterface
{
    protected const ESCAPE_CHAR = '~';

    public function __construct(
        protected ?string $connection = null,
    ) {
    }

    /**
     * @param  array<string, mixed>|null  $context
     */
    public function insert(LogLevel $level, string $message, ?array $context, ?DateTimeInterface $loggedAt): void
    {
        $this->newQuery()->insert([
            'level' => $level->value,
            'message' => $message,
            'context' => $context !== null ? json_encode($context, JSON_THROW_ON_ERROR) : null,
            'logged_at' => $loggedAt ?? new DateTimeImmutable(),
        ]);
    }

    public function query(
        ?LogLevel $level = null,
        ?string $search = null,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        int $limit = 10,
        string $orderDirection = 'desc',
    ): Collection {
        return $this->applyFilters($this->newQuery(), $level, $search, $from, $to)
            ->orderBy('logged_at', $orderDirection)
            ->orderBy('id', $orderDirection)
            ->limit($limit)
            ->get();
    }

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
    ): LengthAwarePaginator {
        return $this->applyFilters($this->newQuery(), $level, $search, $from, $to)
            ->orderBy('logged_at', $orderDirection)
            ->orderBy('id', $orderDirection)
            ->paginate($perPage);
    }

    public function purge(?LogLevel $level = null, ?DateTimeInterface $before = null, int $chunkSize = 0): int
    {
        if ($chunkSize <= 0) {
            return $this->buildPurgeQuery($level, $before)->delete();
        }

        $total = 0;
        do {
            $deleted = $this->buildPurgeQuery($level, $before)->limit($chunkSize)->delete();
            $total += $deleted;
        } while ($deleted === $chunkSize);

        return $total;
    }

    public function stats(): LogStats
    {
        /** @var int $ttl */
        $ttl = config('log-hole.stats_cache_ttl', 0);

        if ($ttl <= 0) {
            return $this->computeStats();
        }

        /** @var LogStats */
        return Cache::remember(
            $this->getStatsCacheKey(),
            $ttl,
            fn (): LogStats => $this->computeStats(),
        );
    }

    public function getTableName(): string
    {
        /** @var string */
        return config('log-hole.database.table', 'logs_hole');
    }

    protected function computeStats(): LogStats
    {
        $results = $this->newQuery()
            ->selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->get();

        $byLevel = [];
        $total = 0;

        foreach ($results as $row) {
            $byLevel[$row->level] = (int) $row->count;
            $total += (int) $row->count;
        }

        return new LogStats(total: $total, byLevel: $byLevel);
    }

    protected function getStatsCacheKey(): string
    {
        return 'log-hole:stats:' . ($this->connection ?? 'default') . ':' . $this->getTableName();
    }

    protected function newQuery(): Builder
    {
        /** @var ?string $connection */
        $connection = $this->connection ?? config('log-hole.connection');

        return $connection !== null
            ? DB::connection($connection)->table($this->getTableName())
            : DB::table($this->getTableName());
    }

    protected function buildPurgeQuery(?LogLevel $level, ?DateTimeInterface $before): Builder
    {
        $query = $this->newQuery();

        $query->when($level !== null, fn (Builder $q) => $q->where('level', $level->value));
        $query->when($before !== null, fn (Builder $q) => $q->where('logged_at', '<', $before));

        return $query;
    }

    protected function applyFilters(
        Builder $query,
        ?LogLevel $level,
        ?string $search,
        ?DateTimeInterface $from,
        ?DateTimeInterface $to,
    ): Builder {
        return $query
            ->when($level !== null, fn (Builder $q) => $q->where('level', $level->value))
            ->when($search !== null && $search !== '', fn (Builder $q) => $this->applySearch($q, $search))
            ->when($from !== null, fn (Builder $q) => $q->where('logged_at', '>=', $from))
            ->when($to !== null, fn (Builder $q) => $q->where('logged_at', '<=', $to));
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        $pattern = '%' . $this->escapeLike($search) . '%';

        return $query->whereRaw(
            'message LIKE ? ESCAPE ?',
            [$pattern, self::ESCAPE_CHAR],
        );
    }

    /**
     * Escape LIKE wildcards using ~ as escape character.
     *
     * Using ~ instead of \ avoids cross-DB inconsistencies with backslash
     * string-literal handling (MySQL vs Postgres standard_conforming_strings).
     */
    protected function escapeLike(string $value): string
    {
        return str_replace(
            [self::ESCAPE_CHAR, '%', '_'],
            [self::ESCAPE_CHAR . self::ESCAPE_CHAR, self::ESCAPE_CHAR . '%', self::ESCAPE_CHAR . '_'],
            $value,
        );
    }
}
