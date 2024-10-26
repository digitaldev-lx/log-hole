<?php

return [
    'database' => [
        'driver' => 'custom',
        'via' => DigitalDevLx\LogHole\Channels\DatabaseChannel::class,
        'level' => env('LOG_LEVEL', 'debug'),
        'table' => 'logs_hole',
    ],
];
