<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Drivers;

use Illuminate\Database\Query\Builder;
use Override;

class MySqlDriver extends RelationalDriver
{
    #[Override]
    protected function applySearch(Builder $query, string $search): Builder
    {
        $escaped = $this->escapeLike($search);

        return $query->where(function (Builder $q) use ($escaped, $search) {
            $q->where('message', 'LIKE', "%{$escaped}%")
                ->orWhereRaw('JSON_SEARCH(context, \'one\', ?) IS NOT NULL', [$search]);
        });
    }
}
