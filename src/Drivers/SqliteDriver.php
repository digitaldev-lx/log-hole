<?php

namespace DigitalDevLx\LogHole\Drivers;

use Illuminate\Database\Query\Builder;

class SqliteDriver extends RelationalDriver
{
    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('message', 'LIKE', "%{$search}%")
                ->orWhere('context', 'LIKE', "%{$search}%");
        });
    }

    protected function truncate(): void
    {
        $this->newQuery()->delete();
    }
}
