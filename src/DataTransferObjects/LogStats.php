<?php

namespace DigitalDevLx\LogHole\DataTransferObjects;

use DigitalDevLx\LogHole\Enums\LogLevel;

readonly class LogStats
{
    /**
     * @param  int  $total
     * @param  array<string, int>  $byLevel
     */
    public function __construct(
        public int $total,
        public array $byLevel,
    ) {
    }

    public function countForLevel(LogLevel $level): int
    {
        return $this->byLevel[$level->value] ?? 0;
    }
}
