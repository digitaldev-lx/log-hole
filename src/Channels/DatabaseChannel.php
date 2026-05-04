<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Channels;

use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Drivers\DriverFactory;
use DigitalDevLx\LogHole\Enums\LogLevel;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Override;
use Throwable;

class DatabaseChannel extends AbstractProcessingHandler
{
    private const ERROR_LOG_INTERVAL = 60;

    private static int $lastErrorLogAt = 0;

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
            $driver = $this->resolveDriver();
            $logLevel = LogLevel::fromMonolog($record->level);

            $driver->insert(
                level: $logLevel,
                message: $record->message,
                context: ! empty($record->context) ? $record->context : null,
                loggedAt: $record->datetime,
            );
        } catch (Throwable $e) {
            $this->logError($e);
        }
    }

    private function resolveDriver(): LogDriverInterface
    {
        if (function_exists('app')) {
            try {
                /** @var LogDriverInterface */
                return app(LogDriverInterface::class);
            } catch (Throwable) {
                // fall back to direct factory if container not booted
            }
        }

        return DriverFactory::make();
    }

    private function logError(Throwable $e): void
    {
        $now = time();

        if ($now - self::$lastErrorLogAt < self::ERROR_LOG_INTERVAL) {
            return;
        }

        self::$lastErrorLogAt = $now;

        error_log(sprintf(
            '[LogHole] Failed to write log to database: [%s] %s in %s:%d',
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
        ));
    }
}
