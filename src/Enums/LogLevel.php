<?php

namespace DigitalDevLx\LogHole\Enums;

use Monolog\Level;

enum LogLevel: string
{
    case Emergency = 'EMERGENCY';
    case Alert = 'ALERT';
    case Critical = 'CRITICAL';
    case Error = 'ERROR';
    case Warning = 'WARNING';
    case Notice = 'NOTICE';
    case Info = 'INFO';
    case Debug = 'DEBUG';

    public function color(): string
    {
        return match ($this) {
            self::Emergency, self::Alert => 'red',
            self::Critical => 'rose',
            self::Error => 'orange',
            self::Warning => 'yellow',
            self::Notice => 'blue',
            self::Info => 'green',
            self::Debug => 'gray',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Emergency, self::Alert => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            self::Critical => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200',
            self::Error => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            self::Warning => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            self::Notice => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            self::Info => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            self::Debug => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }

    public function toMonologLevel(): Level
    {
        return match ($this) {
            self::Emergency => Level::Emergency,
            self::Alert => Level::Alert,
            self::Critical => Level::Critical,
            self::Error => Level::Error,
            self::Warning => Level::Warning,
            self::Notice => Level::Notice,
            self::Info => Level::Info,
            self::Debug => Level::Debug,
        };
    }

    public static function fromMonolog(Level $level): self
    {
        return match ($level) {
            Level::Emergency => self::Emergency,
            Level::Alert => self::Alert,
            Level::Critical => self::Critical,
            Level::Error => self::Error,
            Level::Warning => self::Warning,
            Level::Notice => self::Notice,
            Level::Info => self::Info,
            Level::Debug => self::Debug,
        };
    }

    public static function fromString(string $level): self
    {
        return self::from(strtoupper($level));
    }
}
