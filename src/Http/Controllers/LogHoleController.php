<?php

namespace DigitalDevLx\LogHole\Http\Controllers;

use Carbon\Carbon;
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Http\Request;

class LogHoleController
{
    public function index(Request $request)
    {
        $driver = app(LogDriverInterface::class);

        $level = $request->query('level')
            ? LogLevel::tryFrom(strtoupper($request->query('level')))
            : null;

        $search = $request->query('search');
        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : null;
        $perPage = (int) config('log-hole.per_page', 25);

        $logs = $driver->paginate(
            level: $level,
            search: $search,
            from: $from,
            to: $to,
            perPage: $perPage,
        )->withQueryString();

        $stats = $driver->stats();

        return view('log-hole::dashboard', [
            'logs' => $logs,
            'stats' => $stats,
            'levels' => LogLevel::cases(),
            'filters' => [
                'level' => $request->query('level', ''),
                'search' => $search ?? '',
                'from' => $request->query('from', ''),
                'to' => $request->query('to', ''),
            ],
            'autoRefresh' => config('log-hole.auto_refresh', false),
        ]);
    }
}
