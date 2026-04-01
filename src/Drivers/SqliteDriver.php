<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Drivers;

use Illuminate\Database\Query\Builder;
use Override;

class SqliteDriver extends RelationalDriver
{
    #[Override]
    protected function applySearch(Builder $query, string $search): Builder
    {
        $escaped = $this->escapeLike($search);
        $pattern = "%{$escaped}%";

        return $query->where(function (Builder $q) use ($pattern) {
            $q->whereRaw('message LIKE ? ESCAPE \'\\\'', [$pattern])
                ->orWhereRaw('context LIKE ? ESCAPE \'\\\'', [$pattern]);
        });
    }
}
