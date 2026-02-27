<?php

namespace DigitalDevLx\LogHole\Attributes;

use Attribute;
use DigitalDevLx\LogHole\Enums\LogLevel;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Loggable
{
    public readonly LogLevel $logLevel;

    public function __construct(
        public readonly string $message = '',
        LogLevel|string $level = LogLevel::Info,
        public readonly bool $includeRequest = false,
        public readonly ?string $channel = null,
    ) {
        $this->logLevel = $level instanceof LogLevel
            ? $level
            : (LogLevel::tryFrom(strtoupper($level)) ?? LogLevel::Info);
    }
}
