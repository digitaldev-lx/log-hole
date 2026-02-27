<?php

namespace DigitalDevLx\LogHole\Drivers;

use Illuminate\Database\Query\Builder;

class MySqlDriver extends RelationalDriver
{
    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('message', 'LIKE', "%{$search}%")
                ->orWhereRaw('JSON_EXTRACT(context, \'$\') LIKE ?', ["%{$search}%"]);
        });
    }
}
