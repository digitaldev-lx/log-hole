<?php

namespace DigitalDevLx\LogHole\Channels;

use DigitalDevLx\LogHole\Drivers\DriverFactory;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Throwable;

class DatabaseChannel extends AbstractProcessingHandler
{
    public function __invoke(array $config)
    {
        $level = $config['level'] ?? Level::Debug;

        $logger = new Logger('database');
        $logger->pushHandler(new self($level));

        return $logger;
    }

    protected function write(LogRecord $record): void
    {
        try {
            $driver = DriverFactory::make();
            $logLevel = LogLevel::fromMonolog($record->level);

            $driver->insert(
                level: $logLevel,
                message: $record->message,
                context: ! empty($record->context) ? $record->context : null,
                loggedAt: $record->datetime,
            );
        } catch (Throwable $e) {
            // Prevent infinite loop: do not log to same channel
            // Use error_log as last resort fallback
            error_log("[LogHole] Failed to write log to database: {$e->getMessage()}");
        }
    }
}
