<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Drivers;

use Illuminate\Database\Query\Builder;
use Override;

class SqlServerDriver extends RelationalDriver
{
    #[Override]
    protected function applySearch(Builder $query, string $search): Builder
    {
        $escaped = $this->escapeLike($search);

        return $query->where(function (Builder $q) use ($escaped) {
            $q->where('message', 'LIKE', "%{$escaped}%")
                ->orWhereRaw('CAST(context AS NVARCHAR(MAX)) LIKE ?', ["%{$escaped}%"]);
        });
    }
}
