<?php

namespace DigitalDevLx\LogHole\Commands;

use Carbon\Carbon;
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Console\Command;

class LogHoleCommand extends Command
{
    public $signature = 'log-hole:tail
        {--emergency} {--alert} {--critical} {--error} {--warning} {--notice} {--info} {--debug}
        {--from=} {--to=} {--take=}
        {--purge}';

    public $description = 'Get logs from the database with level filters, date range, or --purge to clear all logs.';

    public function handle(LogDriverInterface $driver): int
    {
        if ($this->option('purge')) {
            if (! $this->confirm('Are you sure you want to purge all logs?')) {
                $this->info('Purge cancelled.');

                return self::SUCCESS;
            }

            $count = $driver->purge();
            $this->info("Purged {$count} log(s) from the database.");

            return self::SUCCESS;
        }

        $level = $this->resolveLevel();
        $from = $this->option('from') ? Carbon::parse($this->option('from'))->startOfDay() : null;
        $to = $this->option('to') ? Carbon::parse($this->option('to'))->endOfDay() : null;
        $take = (int) ($this->option('take') ?? 10);

        $levelLabel = $level !== null ? $level->value : 'ALL';
        $this->info("Fetching {$levelLabel} logs (limit: {$take})");

        $logs = $driver->query(
            level: $level,
            from: $from,
            to: $to,
            limit: $take,
        );

        if ($logs->isEmpty()) {
            $this->warn('No logs found matching your criteria.');

            return self::SUCCESS;
        }

        $this->table(
            ['Level', 'Message', 'Context', 'Logged At'],
            $logs->map(fn (object $log) => [
                $log->level,
                $log->message,
                $log->context !== null
                    ? json_encode(json_decode($log->context, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    : '',
                $log->logged_at,
            ]),
        );

        return self::SUCCESS;
    }

    protected function resolveLevel(): ?LogLevel
    {
        $levels = [
            'emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug',
        ];

        $selected = collect($levels)->first(fn (string $option) => $this->option($option));

        return $selected !== null
            ? LogLevel::fromString($selected)
            : null;
    }
}
