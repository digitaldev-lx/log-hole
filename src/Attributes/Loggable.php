<?php

namespace DigitalDevLx\LogHole\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Loggable
{
    public function __construct(
        public string $message = '',
        public string $level = 'info', // Nível de log, por padrão "info"
    ) {}
}
