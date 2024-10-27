![laravel-eupago-repo-banner](https://pbs.twimg.com/profile_banners/593785558/1671194657/1500x500)

# Laravel LogHole

LogHole is a modern, flexible Laravel logging package that supports Redis and database drivers. Designed for seamless integration with Laravel's Log facade, it leverages PHP attributes for a clean and powerful logging experience.

[![Latest version](https://img.shields.io/github/release/digitaldev-lx/log-hole?style=flat-square)](https://github.com/digitaldev-lx/log-hole/releases)
[![GitHub license](https://img.shields.io/github/license/digitaldev-lx/log-hole?style=flat-square)](https://github.com/digitaldev-lx/log-hole/blob/master/LICENSE)

---

## Requirements

| Release |  PHP   | Laravel |
|---------|:------:|:-------:|
| 1.0.0   | >= 8.2 |   10    |

---

## Installation

Install the package via Composer:
```bash
composer require digitaldev-lx/log-hole
```

You must publish the configuration file:
```bash
php artisan vendor:publish --provider="DigitalDevLx\LogHole\LogHoleServiceProvider" --tag=logs-config
```

## Configurations
In the configuration file, specify the driver you'd like to use (database). By default, the package supports the database driver.

## Example Configuration for Database:

**.env file**
```php
LOG_CHANNEL=database
```

**configuration file logging.php**
```php
'channels' => [
    /*.... */
    'database' => config('log-hole.database'),
],
```

## Use Middleware:

### Laravel 10.x
Laravel 10.x uses the web middleware group by default. To log all requests, add the LogHole middleware to the web group in the app/Http/Kernel.php file:
```php
use DigitalDevLx\LogHole\Middlewares\LogHoleMiddleware;

protected $middlewareGroups = [
    'web' => [
        // outros middlewares
        \DigitalDevLx\LogHole\Middleware\LogHoleMiddleware::class,
    ],
];
```

### Laravel 11.x
Laravel 11.x also uses the web middleware group by default. To log all requests, add the LogHole middleware to the withMiddleware method in the bootstrap/app.php file:
```php
use DigitalDevLx\LogHole\Middlewares\LogHoleMiddleware;
 
->withMiddleware(function (Middleware $middleware) {
     $middleware->append(LogHoleMiddleware::class);
})
```

## Using PHP Attributes

LogHole offers PHP attribute-based logging to automatically log actions when specific attributes are applied to methods or classes. To use the Loggable attribute, ***it is necessary to implement the LogHole middleware***.
```php
use DigitalDevLx\LogHole\Attributes\Loggable;

class ExampleService
{
    #[Loggable(message: 'Executando o método importante', level: 'info')]
    public function metodoImportante()
    {
        // Lógica 
    }
}
```

With the middleware in place, all calls to methods annotated with Loggable will be logged as specified.

## Usage

Log messages through Laravel’s Log facade, which will route logs to your chosen storage driver (Redis or database):
```php
use Illuminate\Support\Facades\Log;

Log::info('This is a log message for LogHole!');
Log::error('An error occurred in LogHole');
```

## View logs

The LogHole dashboard provides a user-friendly interface for viewing logs. You can filter logs by date, level, and message, as well as search for specific log entries.

## Laravel Pail

You can use Laravel Pail to view logs in real-time. Laravel Pail is a powerful tool for monitoring logs and debugging applications. It provides a user-friendly interface for viewing logs in real-time, as well as detailed information about log entries.

# LogHole Tail Command

The `log-hole:tail` command allows you to retrieve logs from the database based on specific log levels or date ranges. This command is highly configurable, enabling you to filter logs by level and date range to get precisely the information you need.

## Usage
To run the `log-hole:tail` command, use the following syntax:
```bash
php artisan log-hole:tail {options}
```

### Options

The command provides several options to customize the logs you want to retrieve:

- **`--all`**: Fetch all logs, regardless of level.
- **`--emergency`**: Fetch only logs with the "EMERGENCY" level.
- **`--critical`**: Fetch only logs with the "CRITICAL" level.
- **`--error`**: Fetch only logs with the "ERROR" level.
- **`--warning`**: Fetch only logs with the "WARNING" level.
- **`--notice`**: Fetch only logs with the "NOTICE" level.
- **`--info`**: Fetch only logs with the "INFO" level.
- **`--debug`**: Fetch only logs with the "DEBUG" level.
- **`--from=`**: Specify the starting date to filter logs, using a date format (e.g., `2024-10-01`).
- **`--to=`**: Specify the end date to filter logs, using a date format (e.g., `2024-10-31`).
- **`--take=`**: Limit the number of log entries displayed (defaults to 10 if not specified).

**Note**: If no specific level is selected, the command will default to retrieving logs at the "ALL" level.

### Examples

Here are some examples of how to use the `log-hole:tail` command effectively:

***Fetch all logs***
```bash
php artisan log-hole:tail --all
```

***Fetch only error-level logs from a specific date range***
```bash
php artisan log-hole:tail --error --from=2024-10-01 --to=2024-10-31
```

***Fetch only warning-level logs***
```bash
php artisan log-hole:tail --warning
```

***Fetch critical logs with a limit of 5 entries***
```bash
php artisan log-hole:tail --critical --take=5
```

***Fetch info-level logs from a specific date onwards***
```bash
php artisan log-hole:tail --info --from=2024-10-01
```

### Output
The command displays logs in a table format with the following columns:

- **ID**: The unique identifier for the log entry.
- **Level**: The log level (e.g., "ERROR", "INFO").
- **Message**: The log message.
- **Context**: Additional context information.
- **Logged At**: The date and time the log entry was created.

A table example is shown below:

```bash
+----+---------+---------------------------------+---------------------------------+---------------------+
| ID | Level   | Message                         | Context                         | Logged At           |
+----+---------+---------------------------------+---------------------------------+---------------------+
| 1  | ERROR   | An error occurred in LogHole    |                                 | 2024-10-01 12:00:00 |
| 2  | WARNING | A warning message from LogHole  |                                 | 2024-10-01 12:00:00 |
| 3  | INFO    | An info message from LogHole    |                                 | 2024-10-01 12:00:00 |
+----+---------+---------------------------------+---------------------------------+---------------------+
```

### Conclusion
LogHole simplifies the logging process in Laravel applications, making it easier to monitor and debug your code. With its powerful features and easy setup, you can enhance your logging experience today.

---

## License
digitaldev-lx/log-hole is open-sourced software licensed under the MIT license.

## About DigitalDev
DigitalDev is a web development agency based in Lisbon, Portugal, specializing in Laravel, Livewire, and Tailwind CSS. Our partnership with Codeboys enables us to provide exceptional solutions tailored to our clients’ needs.
