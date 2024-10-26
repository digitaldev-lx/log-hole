<?php

namespace DigitalDevLx\LogHole\Channels;

use Illuminate\Support\Facades\DB;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

class DatabaseChannel extends AbstractProcessingHandler
{
    public function __invoke(array $config)
    {
        // Define o nível do log com base na configuração, ou "Debug" por padrão
        $level = $config['level'] ?? Level::Debug;

        // Cria o logger e adiciona o handler configurado
        $logger = new Logger('database');
        $logger->pushHandler(new self($level));

        return $logger;
    }

    // Implementa o método `write` usando `LogRecord` como parâmetro
    protected function write(LogRecord $record): void
    {
        DB::table(config('log-hole.database.table'))->insert([
            'level' => $record->level->getName(),
            'message' => $record->message,
            'context' => json_encode($record->context),
            'logged_at' => now()
        ]);
    }
}
