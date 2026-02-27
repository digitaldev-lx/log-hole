<?php

namespace DigitalDevLx\LogHole\Drivers;

use DateTimeInterface;
use DigitalDevLx\LogHole\DataTransferObjects\LogStats;
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RelationalDriver implements LogDriverInterface
{
    public function __construct(
        protected ?string $connection = null,
    ) {
    }

    public function insert(LogLevel $level, string $message, ?array $context, ?DateTimeInterface $loggedAt): void
    {
        $this->newQuery()->insert([
            'level' => $level->value,
            'message' => $message,
            'context' => $context !== null ? json_encode($context) : null,
            'logged_at' => $loggedAt,
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
            ->limit($limit)
            ->get();
    }

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
            ->paginate($perPage);
    }

    public function purge(?LogLevel $level = null, ?DateTimeInterface $before = null): int
    {
        $query = $this->newQuery();

        if ($level === null && $before === null) {
            $count = $query->count();
            $this->truncate();

            return $count;
        }

        $query->when($level !== null, fn (Builder $q) => $q->where('level', $level->value));
        $query->when($before !== null, fn (Builder $q) => $q->where('logged_at', '<', $before));

        return $query->delete();
    }

    public function stats(): LogStats
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

    public function getTableName(): string
    {
        return config('log-hole.database.table', 'logs_hole');
    }

    protected function newQuery(): Builder
    {
        $connection = $this->connection ?? config('log-hole.connection');

        return $connection !== null
            ? DB::connection($connection)->table($this->getTableName())
            : DB::table($this->getTableName());
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
        return $query->where('message', 'LIKE', "%{$search}%");
    }

    protected function truncate(): void
    {
        $this->newQuery()->truncate();
    }
}
