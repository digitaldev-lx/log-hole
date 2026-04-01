<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Tests;

use DigitalDevLx\LogHole\LogHoleServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public static $latestResponse;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LogHoleServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $migration = include __DIR__ . '/../database/migrations/create_logs_table.php';
        $migration->up();
    }
}
