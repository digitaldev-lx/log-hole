<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Drivers;

use Illuminate\Database\Query\Builder;
use Override;

class PostgreSqlDriver extends RelationalDriver
{
    #[Override]
    protected function applySearch(Builder $query, string $search): Builder
    {
        $escaped = $this->escapeLike($search);

        return $query->where(function (Builder $q) use ($escaped) {
            $q->where('message', 'ILIKE', "%{$escaped}%")
                ->orWhereRaw('context::text ILIKE ?', ["%{$escaped}%"]);
        });
    }
}
