<?php

declare(strict_types=1);

return [
    'database' => [
        'driver' => 'custom',
        'via' => DigitalDevLx\LogHole\Channels\DatabaseChannel::class,
        'level' => env('LOG_LEVEL', 'debug'),
        'table' => 'logs_hole',
    ],

    // Database connection to use for logs (null = default connection)
    'connection' => env('LOG_HOLE_DB_CONNECTION', null),

    // Emails of users authorized to access the dashboard (empty = open access)
    'authorized_users' => [],

    // Route prefix for the dashboard
    'dashboard_route' => 'log-hole',

    // Number of logs per page in the dashboard
    'per_page' => 25,

    // Auto-refresh the dashboard
    'auto_refresh' => false,
];
