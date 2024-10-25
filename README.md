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
composer require digitaldev-lx/loghole
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="DigitaldevLx\\LogHole\\LogHoleServiceProvider" --tag=config
```

## Configurations
In the configuration file, specify the driver you'd like to use (Redis or database). By default, the package supports both, allowing you to switch based on your needs.

# Example Configuration for Redis:

```php
'loghole' => [
    'driver' => 'redis',
    'connection' => env('LOGHOLE_REDIS_CONNECTION', 'default'),
    'channel' => env('LOGHOLE_REDIS_CHANNEL', 'loghole_logs')
],
```

# Example Configuration for Database:

```php
'loghole' => [
    'driver' => 'database',
    'table' => env('LOGHOLE_TABLE', 'loghole_logs')
],
```
## Usage

Log messages through Laravel’s Log facade, which will route logs to your chosen storage driver (Redis or database):

```php
use Illuminate\Support\Facades\Log;

Log::info('This is a log message for LogHole!');
Log::error('An error occurred in LogHole');
```

## Using PHP Attributes

LogHole offers PHP attribute-based logging to automatically log actions when specific attributes are applied to methods or classes:

```php
use DigitaldevLx\LogHole\Attributes\Loggable;

#[Loggable(level: 'info')]
public function performAction()
{
    // Action will be logged at 'info' level
}
```

## Advanced Configuration

For fine-grained control, LogHole allows custom attributes for different log levels (e.g., info, warning, error), enabling you to log certain events based on severity.

License
digitaldev-lx/loghole is open-sourced software licensed under the MIT license.

About DigitalDev
DigitalDev is a web development agency based in Lisbon, Portugal, specializing in Laravel, Livewire, and Tailwind CSS. Our partnership with Codeboys enables us to provide exceptional solutions tailored to our clients’ needs.

---


## License

**digitaldev-lx/laravel-eupago** is open-sourced software licensed under the [MIT license](https://github.com/digitaldev-lx/laravel-eupago/blob/master/LICENSE).


## About DigitalDev

[DigitalDev](https://www.digitaldev.pt) is a web development agency based on Lisbon, Portugal. We specialize in Laravel, Livewire, and Tailwind CSS.
[Codeboys](https://www.codeboys.pt) is our special partner and we work together to deliver the best solutions for our clients.



