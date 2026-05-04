![laravel-eupago-repo-banner](https://pbs.twimg.com/profile_banners/593785558/1671194657/1500x500)

# Laravel LogHole

LogHole is a modern, flexible Laravel logging package with multi-driver database support. Designed for seamless integration with Laravel's Log facade, it provides a web dashboard for browsing logs, an Artisan command for CLI access, and PHP 8.4+ attributes for declarative method-level logging.

[![Latest version](https://img.shields.io/github/release/digitaldev-lx/log-hole?style=flat-square)](https://github.com/digitaldev-lx/log-hole/releases)
[![GitHub license](https://img.shields.io/github/license/digitaldev-lx/log-hole?style=flat-square)](https://github.com/digitaldev-lx/log-hole/blob/master/LICENSE)

---

## Features

- Custom Monolog database channel that stores logs in a configurable DB table
- Multi-driver support: MySQL, MariaDB (auto-detected), PostgreSQL, SQLite, SQL Server
- Web dashboard at `/log-hole` with Tailwind CSS v3 and Alpine.js (dark/light mode, filters, stats, auto-refresh)
- Artisan command `log-hole:tail` to query and purge logs from the CLI
- PHP 8.4+ attribute `#[Loggable]` for declarative method and class-level logging via middleware
- LogLevel enum with color-coded badges and Monolog conversion
- Strategy Pattern architecture with `LogDriverInterface` for extensibility
- Cross-database LIKE escaping with explicit `ESCAPE` clause — wildcards in search terms are always treated literally
- Optional stats query cache for dashboards on large logs tables
- Chunked purge for multi-million-row tables

---

## Requirements

| Release | PHP    | Laravel        |
|---------|--------|----------------|
| 4.x     | >= 8.4 | 13.x           |
| 3.x     | >= 8.4 | 11.x, 12.x     |
| 2.x     | >= 8.2 | 10.x, 11.x     |
| 1.x     | >= 8.2 | 10.x           |

**Supported databases:** MySQL, MariaDB, PostgreSQL, SQLite, SQL Server

---

## Installation

Install the package via Composer:

```bash
composer require digitaldev-lx/log-hole
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="log-hole-config"
```

Run the migration to create the `logs_hole` table:

```bash
php artisan migrate
```

---

## Configuration

After publishing, the configuration file is located at `config/log-hole.php`:

```php
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

    // Auto-refresh the dashboard every 5 seconds
    'auto_refresh' => false,

    // Cache TTL (in seconds) for the stats() query. 0 disables caching.
    // Useful when the dashboard auto-refreshes against a large logs table.
    'stats_cache_ttl' => env('LOG_HOLE_STATS_CACHE_TTL', 0),
];
```

### Setting up the log channel

Add the LogHole database channel to your `config/logging.php`:

```php
'channels' => [
    // ... other channels

    'database' => config('log-hole.database'),
],
```

Then set the channel as your default (or use it alongside other channels):

**Option A - Set as default channel in `.env`:**

```env
LOG_CHANNEL=database
```

**Option B - Use as part of a stack:**

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'database'],
    ],

    'database' => config('log-hole.database'),
],
```

**Option C - Log to the database channel on demand:**

```php
use Illuminate\Support\Facades\Log;

Log::channel('database')->info('This goes to the database');
```

### Using a different database connection

If you want logs stored in a different database, set the environment variable:

```env
LOG_HOLE_DB_CONNECTION=mysql_logs
```

Make sure the connection is defined in `config/database.php` and the migration has run on that connection.

### Caching dashboard stats

On a logs table with millions of rows, the per-level `COUNT(*)` query that powers the dashboard's stats bar is expensive. With dashboard auto-refresh on, it runs every 5 seconds per visitor.

To cache the stats query, set a positive TTL:

```env
LOG_HOLE_STATS_CACHE_TTL=5
```

The driver uses Laravel's default cache store under the key `log-hole:stats:{connection}:{table}`.

---

## Usage

### Logging via the Log facade

```php
use Illuminate\Support\Facades\Log;

// If 'database' is your default channel
Log::info('User logged in', ['user_id' => 1]);
Log::error('Payment failed', ['order_id' => 42, 'reason' => 'timeout']);
Log::warning('Disk space running low');

// Or target the database channel explicitly
Log::channel('database')->debug('Debug info', ['context' => 'value']);
```

All standard log levels are supported: `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`.

---

## PHP Attributes

LogHole provides the `#[Loggable]` PHP attribute for declarative logging on controller methods and classes. When the LogHole middleware is active, annotated actions are automatically logged after the request is handled.

### Setup the middleware

In `bootstrap/app.php`:

```php
use DigitalDevLx\LogHole\Middlewares\LogHoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(LogHoleMiddleware::class);
    })
    ->create();
```

> Older Laravel setups (10.x with `app/Http/Kernel.php`) are supported by LogHole `^2.0`. v3 and v4 expect the Laravel 11+ middleware bootstrap layout.

### Method-level attribute

```php
use DigitalDevLx\LogHole\Attributes\Loggable;

class OrderController extends Controller
{
    #[Loggable(message: 'Order was created', level: 'info')]
    public function store(Request $request)
    {
        // ... your logic
    }

    #[Loggable(message: 'Order was deleted', level: 'warning')]
    public function destroy(Order $order)
    {
        // ... your logic
    }
}
```

### Class-level attribute

Apply the attribute to the class to log all controller actions:

```php
use DigitalDevLx\LogHole\Attributes\Loggable;

#[Loggable(level: 'info')]
class UserController extends Controller
{
    public function index() { /* logged automatically */ }
    public function show(User $user) { /* logged automatically */ }
}
```

Method-level attributes take precedence over class-level attributes.

### Attribute options

| Parameter        | Type              | Default        | Description                                      |
|------------------|-------------------|----------------|--------------------------------------------------|
| `message`        | `string`          | `''`           | Custom log message. If empty, uses `"{method} was called"` |
| `level`          | `LogLevel\|string` | `LogLevel::Info` | Log level (enum or string like `'error'`)       |
| `includeRequest` | `bool`            | `false`        | Include request method, URL, and IP in context   |
| `channel`        | `?string`         | `null`         | Target a specific log channel (null = default)   |

### Using the LogLevel enum

```php
use DigitalDevLx\LogHole\Attributes\Loggable;
use DigitalDevLx\LogHole\Enums\LogLevel;

#[Loggable(message: 'Critical action', level: LogLevel::Critical, includeRequest: true)]
public function dangerousAction()
{
    // ...
}
```

---

## Dashboard

The LogHole dashboard provides a modern web interface for browsing and filtering logs.

**URL:** `/log-hole` (configurable via `dashboard_route` in config)

### Features

- **Stats bar** with total count and per-level counters with color-coded badges
- **Server-side filters:** level, search term, date range (from/to)
- **Log table** with level badges, truncated messages with tooltip, expandable JSON context, and relative timestamps
- **Dark/light mode** toggle with localStorage persistence
- **Auto-refresh** toggle (reloads every 5 seconds)
- **Pagination** with Tailwind styling and stable ordering across page boundaries

### Restricting dashboard access

By default, the dashboard is open to everyone. To restrict access, add authorized emails to the config:

```php
'authorized_users' => [
    'admin@example.com',
    'developer@example.com',
],
```

When the list is not empty, only authenticated users with matching emails can access the dashboard. Unauthenticated users or users not in the list will receive a 403 error.

### Gate authorization

LogHole also registers a `viewLogHole` Gate that you can use in your own authorization logic:

```php
if (Gate::allows('viewLogHole')) {
    // user can view logs
}
```

---

## Artisan Command

The `log-hole:tail` command allows you to query and purge logs directly from the CLI.

```bash
php artisan log-hole:tail {options}
```

### Options

| Option          | Description                                              |
|-----------------|----------------------------------------------------------|
| `--emergency`   | Filter by EMERGENCY level                                |
| `--alert`       | Filter by ALERT level                                    |
| `--critical`    | Filter by CRITICAL level                                 |
| `--error`       | Filter by ERROR level                                    |
| `--warning`     | Filter by WARNING level                                  |
| `--notice`      | Filter by NOTICE level                                   |
| `--info`        | Filter by INFO level                                     |
| `--debug`       | Filter by DEBUG level                                    |
| `--from=`       | Start date filter (e.g., `2024-10-01`)                   |
| `--to=`         | End date filter (e.g., `2024-10-31`)                     |
| `--take=`       | Limit the number of entries (default: 10, clamped 1-1000) |
| `--purge`       | Purge all logs (asks for confirmation)                   |

If no level option is specified, all levels are returned.

### Examples

Fetch the last 10 logs (default):

```bash
php artisan log-hole:tail
```

Fetch error-level logs from a date range:

```bash
php artisan log-hole:tail --error --from=2024-10-01 --to=2024-10-31
```

Fetch the last 5 critical logs:

```bash
php artisan log-hole:tail --critical --take=5
```

Purge all logs (with confirmation prompt):

```bash
php artisan log-hole:tail --purge
```

### Output

The command displays logs in a table with the following columns:

```
+-----------+----------------------------------+----------------------------+---------------------+
| Level     | Message                          | Context                    | Logged At           |
+-----------+----------------------------------+----------------------------+---------------------+
| ERROR     | Payment failed                   | {                          | 2024-10-15 14:30:00 |
|           |                                  |     "order_id": 42,        |                     |
|           |                                  |     "reason": "timeout"    |                     |
|           |                                  | }                          |                     |
| WARNING   | Disk space running low           |                            | 2024-10-15 14:25:00 |
| INFO      | User logged in                   | {                          | 2024-10-15 14:20:00 |
|           |                                  |     "user_id": 1           |                     |
|           |                                  | }                          |                     |
+-----------+----------------------------------+----------------------------+---------------------+
```

Context is displayed as pretty-printed JSON for readability.

---

## Driver Architecture

LogHole uses the Strategy Pattern for database access. All drivers implement `LogDriverInterface`:

```php
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
```

The `DriverFactory` auto-detects your database driver and returns the appropriate implementation:

| Database   | Driver Class       | Context search strategy                    |
|------------|--------------------|--------------------------------------------|
| MySQL      | `MySqlDriver`      | `CAST(context AS CHAR) LIKE ? ESCAPE ?`    |
| MariaDB    | `MariaDbDriver`    | Inherits MySQL strategy. Auto-detected via PDO version string, or matched by the explicit `mariadb` driver name |
| PostgreSQL | `PostgreSqlDriver` | `context::text ILIKE ? ESCAPE ?` (case-insensitive Unicode) |
| SQLite     | `SqliteDriver`     | `IFNULL(context, '') LIKE ? ESCAPE ?`      |
| SQL Server | `SqlServerDriver`  | `CAST(context AS NVARCHAR(MAX)) LIKE ? ESCAPE ?` |

All drivers use `~` as the LIKE escape character — chosen for portability across MySQL/Postgres/SQLite/SQL Server string-literal handling. User-facing search semantics are unchanged: `%`, `_`, and `~` typed into the search box are matched literally.

The driver is registered as a singleton in the service container. You can resolve it directly:

```php
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Enums\LogLevel;

$driver = app(LogDriverInterface::class);

// Insert a log
$driver->insert(LogLevel::Info, 'Hello', ['key' => 'value'], now());

// Query logs with filters
$logs = $driver->query(level: LogLevel::Error, search: 'payment', limit: 20);

// Get paginated results
$paginated = $driver->paginate(level: LogLevel::Warning, perPage: 25);

// Get stats (cached when log-hole.stats_cache_ttl > 0)
$stats = $driver->stats();
echo $stats->total;
echo $stats->countForLevel(LogLevel::Error);

// Purge logs
$driver->purge();                                      // purge all
$driver->purge(level: LogLevel::Debug);                // purge only debug logs
$driver->purge(before: now()->subMonth());             // purge logs older than 1 month
$driver->purge(before: now()->subYear(), chunkSize: 5000); // batched delete
```

The `chunkSize` argument on `purge()` lets you delete in batches instead of one statement — recommended on multi-million-row tables to reduce lock contention and binlog volume. Pass `0` (default) for a single `DELETE` statement.

---

## Database Table

The migration creates a `logs_hole` table (configurable via `config('log-hole.database.table')`) with the following structure:

| Column      | Type     | Notes                              |
|-------------|----------|------------------------------------|
| `id`        | bigint   | Primary key, auto-increment        |
| `message`   | text     | Log message                        |
| `level`     | string   | Log level (e.g., `ERROR`)          |
| `context`   | json     | Nullable, additional context       |
| `logged_at` | datetime | Nullable, when the log was created |

Indexes: `logged_at`, and a composite `(level, logged_at)` that also covers level-only filters via leftmost-prefix.

---

## Upgrading

### v3.x → v4.0

`v4` is a Laravel 13 release with no public API breaks. The driver layer was overhauled internally to fix LIKE wildcard escaping bugs in PostgreSQL and SQL Server, and to unify search semantics across MySQL/MariaDB. If you only used the Log facade, the dashboard, the `log-hole:tail` command or `#[Loggable]`, the upgrade is `composer require digitaldev-lx/log-hole:^4.0`.

If you implemented a custom driver against `LogDriverInterface`, note that `purge()` now accepts a third optional argument `int $chunkSize = 0`. Existing callers continue to work; the new argument has a backward-compatible default.

### v1.x → v2.0

1. **Config file structure changed** — re-publish the config:
   ```bash
   php artisan vendor:publish --tag="log-hole-config" --force
   ```

2. **Publish tag renamed** — changed from `--tag=logs-config` to `--tag=log-hole-config` (Spatie convention)

3. **`#[Loggable]` attribute** — the `level` property is now a `LogLevel` enum (string values still work for backward compatibility). The `$level` public property was removed in favor of `$logLevel` (readonly `LogLevel` enum).

4. **Views and routes moved** — views moved from `src/resources/views/` to `resources/views/`. Routes moved from `src/routes/` to `routes/`. If you published views, re-publish them.

5. **Migration indexes** — re-run migrations to add the new performance indexes:
   ```bash
   php artisan migrate
   ```

---

## What's new in v4.0

- **Laravel 13 support** (Pest 4, Orchestra Testbench 11.1, Larastan 3.9)
- **Bug fix:** PostgreSQL and SQL Server now emit an `ESCAPE` clause — wildcards in the search term are no longer ignored
- **Bug fix:** MySQL/MariaDB use `CAST(context AS CHAR) LIKE ? ESCAPE ?` instead of `JSON_SEARCH`, giving identical substring semantics across `message` and `context`
- **Bug fix:** Pagination is now stable when many rows share the same `logged_at` (added `id` tiebreaker)
- **Performance:** `DatabaseChannel` resolves the driver from the container singleton on each write; `DriverFactory::isMariaDb()` is cached per-connection
- **Performance:** `stats_cache_ttl` config option for caching the dashboard stats query
- **Robustness:** chunked `purge()`; `insert()` falls back to `now()` when `loggedAt` is null; `error_log` fallback in the channel is rate-limited
- **Tests:** 175 tests on PHPStan level 6, plus a separate integration suite that runs against real Postgres 16 and MySQL 8.4 services in CI

See [CHANGELOG.md](CHANGELOG.md) for the full list.

---

## Testing

```bash
composer run test           # Run default test suite (Pest, SQLite in-memory)
composer run test-coverage  # Tests with coverage report
composer run analyse        # PHPStan level 6
composer run format         # Laravel Pint (PSR-12)
composer run check          # analyse + format together
```

To run the integration suite against a real Postgres or MySQL database (skipped by default), set `LOG_HOLE_INTEGRATION_DB`:

```bash
LOG_HOLE_INTEGRATION_DB=pgsql DB_HOST=127.0.0.1 DB_PORT=5432 \
DB_DATABASE=log_hole_test DB_USERNAME=postgres DB_PASSWORD=postgres \
vendor/bin/pest --testsuite=integration
```

CI runs this suite automatically against Postgres 16 and MySQL 8.4 services.

---

## License

digitaldev-lx/log-hole is open-sourced software licensed under the [MIT license](LICENSE.md).

## About DigitalDev

[DigitalDev](https://www.digitaldev.pt) is a web development agency based in Lisbon, Portugal. We specialize in Laravel, Livewire, and Tailwind CSS.

[Codeboys](https://www.codeboys.pt) is our special partner and we work together to deliver the best solutions for our clients.
