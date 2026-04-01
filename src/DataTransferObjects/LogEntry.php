<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\DataTransferObjects;

use Carbon\Carbon;
use DigitalDevLx\LogHole\Enums\LogLevel;

readonly class LogEntry
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        public int $id,
        public LogLevel $level,
        public string $message,
        public ?array $context,
        public ?Carbon $loggedAt,
    ) {
    }

    public static function fromRow(object $row): self
    {
        return new self(
            id: (int) $row->id,
            level: LogLevel::tryFrom(strtoupper($row->level)) ?? LogLevel::Debug,
            message: $row->message,
            context: $row->context !== null ? json_decode($row->context, true) : null,
            loggedAt: $row->logged_at !== null ? Carbon::parse($row->logged_at) : null,
        );
    }
}
