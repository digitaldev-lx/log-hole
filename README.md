![laravel-loghole-repo-banner](https://pbs.twimg.com/profile_banners/593785558/1671194657/1500x500)

# Laravel LogHole

LogHole is a modern, flexible Laravel logging package that supports Redis and database drivers. Designed for seamless integration with Laravel's Log facade, it leverages PHP attributes for a clean and powerful logging experience.

[![Latest version](https://img.shields.io/github/release/digitaldev-lx/loghole?style=flat-square)](https://github.com/digitaldev-lx/loghole/releases)
[![GitHub license](https://img.shields.io/github/license/digitaldev-lx/loghole?style=flat-square)](https://github.com/digitaldev-lx/loghole/blob/master/LICENSE)

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
php artisan vendor:publish --provider="DigitalDevLx\\LogHole\\LogHoleServiceProvider" --tag=logs-config
```

## Configurations
In the configuration file, specify the driver you'd like to use (database). By default, the package supports the database driver.


## Example Configuration for Database:

.env file
```php
LOG_CHANNEL=database
```

configuration file logging.php
```php
'channels' => [
    /*.... */
    'database' => config('log-hole.database'),
],

```

## Use Middleware:

## Laravel 10.x
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

## Laravel 11.x
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


## Advanced Configuration

For fine-grained control, LogHole allows custom attributes for different log levels (e.g., info, warning, error), enabling you to log certain events based on severity.

---

## License

**digitaldev-lx/log-hole** is open-sourced software licensed under the [MIT license](https://github.com/digitaldev-lx/laravel-eupago/blob/master/LICENSE).


## About DigitalDev

[DigitalDev](https://www.digitaldev.pt) is a web development agency based on Lisbon, Portugal. We specialize in Laravel, Livewire, and Tailwind CSS.
[Codeboys](https://www.codeboys.pt) is our special partner and we work together to deliver the best solutions for our clients.



