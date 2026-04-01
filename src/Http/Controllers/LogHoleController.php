<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Http\Controllers;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LogHoleController
{
    public function index(Request $request): View
    {
        $driver = app(LogDriverInterface::class);

        $levelParam = $request->query('level');
        $level = is_string($levelParam) && $levelParam !== ''
            ? LogLevel::tryFrom(strtoupper($levelParam))
            : null;

        $search = is_string($request->query('search')) ? $request->query('search') : null;
        $from = $this->parseDate($request->query('from'))?->startOfDay();
        $to = $this->parseDate($request->query('to'))?->endOfDay();
        /** @var int $perPage */
        $perPage = config('log-hole.per_page', 25);

        $logs = $driver->paginate(
            level: $level,
            search: $search,
            from: $from,
            to: $to,
            perPage: $perPage,
        )->withQueryString();

        $stats = $driver->stats();

        return view('log-hole::dashboard', [/** @phpstan-ignore argument.type */
            'logs' => $logs,
            'stats' => $stats,
            'levels' => LogLevel::cases(),
            'filters' => [
                'level' => is_string($levelParam) ? $levelParam : '',
                'search' => $search ?? '',
                'from' => is_string($request->query('from')) ? $request->query('from') : '',
                'to' => is_string($request->query('to')) ? $request->query('to') : '',
            ],
            'autoRefresh' => config('log-hole.auto_refresh', false),
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (InvalidFormatException) {
            return null;
        }
    }
}
