<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Channels;

use DigitalDevLx\LogHole\Drivers\DriverFactory;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Throwable;
use Override;

class DatabaseChannel extends AbstractProcessingHandler
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __invoke(array $config): Logger
    {
        $level = $config['level'] ?? Level::Debug;

        $logger = new Logger('database');
        $logger->pushHandler(new self($level));

        return $logger;
    }

    #[Override]
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
            error_log(sprintf(
                '[LogHole] Failed to write log to database: [%s] %s in %s:%d',
                $e::class,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
            ));
        }
    }
}
