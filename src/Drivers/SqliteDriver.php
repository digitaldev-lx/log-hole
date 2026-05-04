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
        $pattern = '%' . $this->escapeLike($search) . '%';
        $escape = self::ESCAPE_CHAR;

        return $query->where(function (Builder $q) use ($pattern, $escape) {
            $q->whereRaw('message LIKE ? ESCAPE ?', [$pattern, $escape])
                ->orWhereRaw('IFNULL(context, \'\') LIKE ? ESCAPE ?', [$pattern, $escape]);
        });
    }
}
