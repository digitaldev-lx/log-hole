<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Tests\Integration;

use DigitalDevLx\LogHole\Drivers\DriverFactory;
use DigitalDevLx\LogHole\LogHoleServiceProvider;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use RuntimeException;

class IntegrationTestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [LogHoleServiceProvider::class];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        // Always provide a default SQLite connection so Orchestra Testbench
        // can boot even when integration driver is not configured.
        $app['config']->set('database.default', 'integration');

        $driver = $this->integrationDriver();
        $config = $driver !== null
            ? $this->connectionConfig($driver)
            : ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''];

        $app['config']->set('database.connections.integration', $config);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->integrationDriver() === null) {
            $this->markTestSkipped(
                'Integration tests skipped — set LOG_HOLE_INTEGRATION_DB to "pgsql" or "mysql" to enable.',
            );
        }

        DriverFactory::clearCache();

        Schema::dropIfExists('logs_hole');
        $migration = include __DIR__ . '/../../database/migrations/create_logs_table.php';
        $migration->up();
    }

    protected function tearDown(): void
    {
        if ($this->integrationDriver() !== null) {
            Schema::dropIfExists('logs_hole');
        }

        parent::tearDown();
    }

    protected function integrationDriver(): ?string
    {
        $value = getenv('LOG_HOLE_INTEGRATION_DB');

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function connectionConfig(string $driver): array
    {
        return match ($driver) {
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => getenv('DB_HOST') ?: '127.0.0.1',
                'port' => getenv('DB_PORT') ?: '5432',
                'database' => getenv('DB_DATABASE') ?: 'log_hole_test',
                'username' => getenv('DB_USERNAME') ?: 'postgres',
                'password' => getenv('DB_PASSWORD') ?: 'postgres',
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => getenv('DB_HOST') ?: '127.0.0.1',
                'port' => getenv('DB_PORT') ?: '3306',
                'database' => getenv('DB_DATABASE') ?: 'log_hole_test',
                'username' => getenv('DB_USERNAME') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: 'root',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
            default => throw new RuntimeException("Unsupported integration driver: {$driver}"),
        };
    }
}
