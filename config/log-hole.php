<?php

return [
    'database' => [
        'driver' => 'custom',
        'via' => DigitalDevLx\LogHole\Channels\DatabaseChannel::class,
        'level' => env('LOG_LEVEL', 'debug'),
        'table' => 'logs_hole',
    ],
    'authorized_users' => [
        // add the email of the authorized users
    ],
    'dashboard_route' => 'log-hole',
];
