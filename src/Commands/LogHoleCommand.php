<?php

namespace DigitalDevLx\LogHole\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LogHoleCommand extends Command
{
    public $signature = 'log-hole:tail {--emergency} {--critical} {--error} {--warning} {--notice} {--info} {--debug} {--from=} {--to=} {--take=} {--purge}';

    public $description = 'Get logs from the database with options --emergency, --critical, --error, --warning, --notice, --info, or --debug. Use --from and --to to filter by date, or --purge to clear all logs.';

    public function handle(): int
    {
        // Se a opção --purge for usada, esvazia a tabela de logs
        if ($this->option('purge')) {
            DB::table(config('log-hole.database.table'))->truncate();
            $this->info("All logs have been purged from the database.");
            return 0;
        }

        // Define o nível de log com base nas opções fornecidas, ou "todos" se nenhum nível for selecionado
        $level = collect([
            'emergency', 'critical', 'error', 'warning', 'notice', 'info', 'debug',
        ])->first(fn ($option) => $this->option($option), null);

        $from = $this->option('from');
        $to = $this->option('to');
        $take = $this->option('take') ?? 10;

        $this->info("Getting logs" . ($level ? " with level " . strtoupper($level) : " for all levels") . " from {$from} to {$to}");

        // Constrói a consulta de logs com ou sem o filtro de nível
        $logs = DB::table(config('log-hole.database.table'))
            ->when($level, function ($query) use ($level) {
                return $query->where('level', strtoupper($level));
            })
            ->when($from, function ($query) use ($from) {
                return $query->where('logged_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                return $query->where('logged_at', '<=', $to);
            })
            ->orderBy('logged_at', 'desc')
            ->take($take)
            ->get();

        // Exibe os logs em formato de tabela
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
