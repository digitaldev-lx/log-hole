<?php

declare(strict_types=1);

namespace DigitalDevLx\LogHole\Drivers;

use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class DriverFactory
{
    public static function make(?string $connection = null): LogDriverInterface
    {
        /** @var ?string $connection */
        $connection = $connection ?? config('log-hole.connection');

        $driverName = self::detectDriver($connection);

        return match ($driverName) {
            'mysql' => self::isMariaDb($connection) ? new MariaDbDriver($connection) : new MySqlDriver($connection),
            'pgsql' => new PostgreSqlDriver($connection),
            'sqlite' => new SqliteDriver($connection),
            'sqlsrv' => new SqlServerDriver($connection),
            default => new RelationalDriver($connection),
        };
    }

    protected static function detectDriver(?string $connection): string
    {
        /** @var string $connectionName */
        $connectionName = $connection ?? config('database.default');

        /** @var string */
        return config("database.connections.{$connectionName}.driver", 'mysql');
    }

    protected static function isMariaDb(?string $connection): bool
    {
        try {
            $pdo = DB::connection($connection)->getPdo();
            $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

            return str_contains(strtolower($version), 'mariadb');
        } catch (Throwable) {
            return false;
        }
    }
}
