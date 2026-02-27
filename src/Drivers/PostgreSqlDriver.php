<?php

namespace DigitalDevLx\LogHole\Drivers;

use Illuminate\Database\Query\Builder;

class PostgreSqlDriver extends RelationalDriver
{
    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('message', 'ILIKE', "%{$search}%")
                ->orWhereRaw('context::text ILIKE ?', ["%{$search}%"]);
        });
    }
}
