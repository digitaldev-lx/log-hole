# Changelog

All notable changes to `digitaldev-lx/log-hole` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2026-04-01

### Added

- **153 Pest tests** (up from 95) covering search escaping, purge atomicity, middleware attribute detection, controller edge cases, and all command flags
- **LIKE wildcard escaping** via `escapeLike()` in all drivers — `%`, `_`, and `\` in search terms are now properly escaped
- **SQLite ESCAPE clause** — `ESCAPE '\'` added to LIKE queries for correct wildcard escaping
- **Date validation** in controller and command — `Carbon::parse()` wrapped in try-catch, invalid dates are silently ignored instead of causing 500 errors
- **Input type safety** — controller validates `search` and `level` params are strings, rejects array injection
- **`--take` clamping** in `log-hole:tail` — value clamped between 1 and 1000
- **Null controller guard** in `LogHoleMiddleware` — prevents ReflectionClass error on misconfigured routes

### Changed

- **PHP minimum version raised to 8.4** (from 8.2)
- **Laravel 10 support dropped** — now supports Laravel 11.x and 12.x
- **PHPStan raised to level 6** with Larastan 3.x and PHPStan 2.x
- **Pest upgraded to 3.x** (from 2.x) with matching plugin versions
- **`declare(strict_types=1)` enforced** across all PHP files via Pint
- **`#[\Override]` attribute** added to all overriding methods in drivers and DatabaseChannel
- **`array_find()`** (PHP 8.4) replaces `collect()->first()` in `LogHoleCommand::resolveLevel()`
- **`str_contains()`** replaces `stripos()` in `DriverFactory::isMariaDb()`
- **`Log::getFacadeRoot()`** replaced with `Log::driver()` in LogHoleMiddleware
- **Return type declarations** added to middleware `handle()`, `DatabaseChannel::__invoke()`, `LogHoleController::index()`
- **CI matrix** updated: PHP 8.4, Laravel 11/12, Testbench 9/10
- **Larastan** upgraded to v3.x (PHPStan 2.x ecosystem)
- **`LogHoleDashboardAccessMiddleware`** now uses the `viewLogHole` Gate instead of duplicating authorization logic
- **`DatabaseChannel` error fallback** now includes exception class, file, and line number
- **MySQL JSON search** uses `JSON_SEARCH()` instead of inefficient `JSON_EXTRACT()` with LIKE
- **`json_encode()` in insert** now uses `JSON_THROW_ON_ERROR` to fail loudly instead of silently inserting `false`

### Fixed

- **LIKE wildcard injection** — search terms containing `%` or `_` no longer act as SQL wildcards across all drivers
- **Race condition in `purge()`** — replaced non-atomic `count()` + `truncate()` with single `delete()` that returns affected rows
- **Duplicate authorization logic** — middleware and service provider shared identical email extraction code; middleware now delegates to the Gate
- **Redundant database index** — removed individual `level` index (covered by composite `['level', 'logged_at']`)

### Removed

- **PHP 8.2/8.3 support** — minimum is now PHP 8.4
- **Laravel 10 support** — minimum is now Laravel 11
- **`truncate()` method** — removed from `RelationalDriver` and `SqliteDriver` (purge always uses `delete()` now)

## [2.0.0] - 2026-02-27

### Added

- **Multi-driver database support** - MySQL, MariaDB (auto-detected), PostgreSQL, SQLite, SQL Server via Strategy Pattern architecture
- **LogLevel enum** (`src/Enums/LogLevel.php`) with color-coded badges, Monolog conversion, and string parsing
- **LogEntry DTO** (`src/DataTransferObjects/LogEntry.php`) - readonly data transfer object for log entries
- **LogStats DTO** (`src/DataTransferObjects/LogStats.php`) - readonly data transfer object for log statistics
- **LogDriverInterface** (`src/Drivers/Contracts/LogDriverInterface.php`) with `insert()`, `query()`, `paginate()`, `purge()`, `stats()` methods
- **RelationalDriver** base class and 5 database-specific drivers (MySqlDriver, MariaDbDriver, PostgreSqlDriver, SqliteDriver, SqlServerDriver)
- **DriverFactory** with auto-detection of database driver including MariaDB detection via PDO version string
- **Redesigned dashboard** with Tailwind CSS v3 CDN + Alpine.js v3:
  - Stats bar with total and per-level counters
  - Server-side filters: level, search, date range (from/to)
  - Color-coded level badges
  - Expandable JSON context viewer
  - Relative timestamps (`diffForHumans()`) with full datetime tooltip
  - Dark/light mode toggle with localStorage persistence
  - Auto-refresh toggle (5 second interval)
  - Empty state with contextual message
  - Custom Tailwind pagination view
- **Class-level `#[Loggable]` attribute** - apply to a controller to log all actions
- **`includeRequest` option** on `#[Loggable]` - logs HTTP method, URL, and IP in context
- **`channel` option** on `#[Loggable]` - target a specific log channel
- **`--alert` flag** on `log-hole:tail` command
- **Confirmation prompt** before `--purge` in the Artisan command
- **Pretty-printed JSON context** in CLI output
- **Configurable database connection** via `LOG_HOLE_DB_CONNECTION` env variable
- **`per_page` config option** for dashboard pagination (default: 25)
- **`auto_refresh` config option** for dashboard auto-refresh
- **Database indexes** on `level`, `logged_at`, and composite `(level, logged_at)` for query performance
- **LogDriverInterface singleton** registered in the service container
- **95 Pest tests** covering enums, DTOs, attributes, channels, drivers, factory, middlewares, controller, command, service provider, routes, and architecture
- **PHPStan level 5** static analysis with zero errors
- **CHANGELOG.md**

### Changed

- **`Loggable` attribute** now accepts `LogLevel|string` for the level parameter (backward compatible with string)
- **`Loggable` attribute** properties are now `readonly`
- **`Loggable` attribute** target expanded from `TARGET_METHOD` to `TARGET_METHOD | TARGET_CLASS`
- **`DatabaseChannel::write()`** now delegates to `DriverFactory::make()->insert()` instead of direct `DB::table()` calls
- **`DatabaseChannel::write()`** uses `$record->datetime` instead of `now()` for `logged_at`
- **`DatabaseChannel::write()`** wraps in try/catch with `error_log()` fallback to prevent infinite loops
- **`LogHoleMiddleware`** uses `LogLevel` enum, supports class-level attributes, protects against closure routes
- **`LogHoleController`** injects `LogDriverInterface`, accepts query params (level, search, from, to), passes stats to view
- **`LogHoleCommand`** uses `LogDriverInterface` instead of direct `DB::table()` calls
- **`LogHoleServiceProvider`** uses Spatie PackageServiceProvider methods (`hasConfigFile()`, `hasViews()`, `hasRoute()`)
- **Views** moved from `src/resources/views/` to `resources/views/` (Spatie convention)
- **Routes** moved from `src/routes/web.php` to `routes/web.php` (Spatie convention)
- **Routes** wrapped in `Route::middleware('web')` group
- **Dashboard** upgraded from Tailwind CSS v2 CDN to Tailwind CSS v3 CDN
- **Config publish tag** changed from `logs-config` to `log-hole-config`
- **Migration** now includes indexes and uses `config()` fallback defaults
- **composer.json** updated description and keywords, removed redundant autoload entries

### Fixed

- **Config key mismatch** - `LogHoleDashboardAccessMiddleware` referenced `config('log-hole.dashboard_authorized_users')` but config defined `authorized_users` (without prefix)
- **Null user crash** - `LogHoleDashboardAccessMiddleware` called `$user->email` without null check
- **Non-strict comparison** - `in_array()` in middleware now uses strict comparison
- **Invisible text** - Dashboard header had `text-white` on `bg-white`, table cells had `text-gray-200` on white background
- **TestCase migration** - Was commented out and referenced wrong filename
- **ServiceProvider Gate** - `viewLogHole` Gate now accepts nullable user parameter
- **ServiceProvider** now calls parent boot via Spatie's `bootingPackage()` hook

### Removed

- **`database/factories/ModelFactory.php`** - entirely commented out, unused
- **`public/css/app.css`** - unused (dashboard uses CDN)
- **Redundant PSR-4 autoload** - `DigitalDevLx\LogHole\Attributes\` entry (already covered by root `src/` mapping)
- **Client-side search** - replaced inline `searchTable()` JavaScript with server-side filtering

## [1.3.0] - 2024-10-29

### Added

- Dashboard access middleware with authorized users configuration
- Dashboard UI improvements

## [1.2.0] - 2024-10-28

### Added

- Web dashboard for browsing logs
- `--purge` option to clear all logs from the database

## [1.1.0] - 2024-10-27

### Added

- `log-hole:tail` Artisan command with level and date filters
- `--take` option to limit results
- `--from` and `--to` date range filtering

## [1.0.0] - 2024-10-26

### Added

- Initial release
- Custom Monolog database channel
- `#[Loggable]` PHP attribute for method-level logging
- LogHole middleware for attribute detection
- Database migration for `logs_hole` table
- Package configuration file

[3.0.0]: https://github.com/digitaldev-lx/log-hole/compare/v2.0.0...v3.0.0
[2.0.0]: https://github.com/digitaldev-lx/log-hole/compare/v1.3.0...v2.0.0
[1.3.0]: https://github.com/digitaldev-lx/log-hole/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/digitaldev-lx/log-hole/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/digitaldev-lx/log-hole/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/digitaldev-lx/log-hole/releases/tag/v1.0.0
