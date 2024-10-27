<?php

namespace DigitalDevLx\LogHole\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LogHoleCommand extends Command
{
    public $signature = 'log-hole:tail {--all} {--emergency} {--critical} {--error} {--warning} {--notice} {--info} {--debug} {--from=} {--to=} {--take=}';

    public $description = 'Get logs from the database with flag --all, --emergency, --critical, --error, --warning, --notice, --info or --debug. You can also use --from and --to to filter by date.';

    public function handle(): int
    {

        $level = collect([
            'all', 'emergency', 'critical', 'error', 'warning', 'notice', 'info', 'debug',
        ])->first(fn ($option) => $this->option($option), 'all');

        $from = $this->option('from');
        $to = $this->option('to');
        $take = $this->option('take') ?? 10;

        $this->info("Getting logs with level {$level} from {$from} to {$to}");

        // converter level em uppercase
        $level = strtoupper($level);

        $logs = DB::table(config('log-hole.database.table'))
            ->where('level', $level)
            ->when($from, function ($query) use ($from) {
                return $query->where('logged_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                return $query->where('logged_at', '<=', $to);
            })
            ->orderBy('logged_at', 'desc')
            ->take($take)
            ->get();

        $this->table(['Level', 'Message', 'Context', 'Logged At'], $logs->map(function ($log) {
            return [
                $log->level,
                $log->message,
                $log->context,
                $log->logged_at,
            ];
        }));

        return 0; // Retorna 0 para indicar que o comando foi executado com sucesso
    }
}
