<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Attributes\Loggable;
use DigitalDevLx\LogHole\Enums\LogLevel;
use DigitalDevLx\LogHole\Middlewares\LogHoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

// ---------------------------------------------------------------------------
// Named stub controllers required for class-level attribute tests.
// PHP does not allow #[Attribute] syntax on variable assignments, so
// controllers that carry a class-level #[Loggable] must be declared as
// top-level named classes.
// ---------------------------------------------------------------------------

#[Loggable(message: 'controller action called', level: LogLevel::Warning)]
class ClassLevelLoggableController
{
    public function index(): void
    {
    }
}

#[Loggable(message: 'class-level message', level: LogLevel::Debug)]
class ClassAndMethodLoggableController
{
    #[Loggable(message: 'method-level message', level: LogLevel::Error)]
    public function update(): void
    {
    }
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Build a mocked Route + Request pair for the given controller and action.
 */
function makeRequestWithRoute(object $controller, string $method, string $action): Request
{
    $route = Mockery::mock(Route::class);
    $route->shouldReceive('getAction')->with('uses')->andReturn(get_class($controller) . '@' . $action);
    $route->shouldReceive('getController')->andReturn($controller);
    $route->shouldReceive('getActionMethod')->andReturn($action);

    $request = Request::create('/test', $method);
    $request->setRouteResolver(fn () => $route);

    return $request;
}

/**
 * Create a Mockery spy that satisfies LoggerInterface and stub Log::driver()
 * to return it. Returns the spy so callers can assert on it.
 *
 * The middleware resolves the logger via Log::driver() (no channel) or
 * Log::channel($name) (with channel). Log::spy() only intercepts direct
 * facade calls (e.g. Log::info()), not the chained ->log() on the returned
 * logger object, so we must stub driver() explicitly.
 */
function makeLoggerSpy(): LoggerInterface
{
    $spy = Mockery::spy(LoggerInterface::class);

    Log::shouldReceive('driver')->andReturn($spy);

    return $spy;
}

// ---------------------------------------------------------------------------
// Method-level attribute detection
// ---------------------------------------------------------------------------

it('detects method-level Loggable attribute and writes a log', function () {
    $logger = makeLoggerSpy();

    $controller = new class () {
        #[Loggable(message: 'order was placed', level: LogLevel::Info)]
        public function store(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'POST', 'store');

    $response = (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');

    $logger->shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $level === 'info' && $message === 'order was placed');
});

it('detects class-level Loggable attribute when the method has no attribute', function () {
    $logger = makeLoggerSpy();

    $controller = new ClassLevelLoggableController();
    $request = makeRequestWithRoute($controller, 'GET', 'index');

    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    $logger->shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $level === 'warning' && $message === 'controller action called');
});

it('method-level attribute takes priority over class-level attribute', function () {
    $logger = makeLoggerSpy();

    $controller = new ClassAndMethodLoggableController();
    $request = makeRequestWithRoute($controller, 'PUT', 'update');

    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    // Only one log call, using method-level values
    $logger->shouldHaveReceived('log')->once();

    $logger->shouldHaveReceived('log')
        ->withArgs(fn ($level, $message) => $level === 'error' && $message === 'method-level message');
});

// ---------------------------------------------------------------------------
// includeRequest context
// ---------------------------------------------------------------------------

it('includes method, url, and ip in context when includeRequest is true', function () {
    $logger = makeLoggerSpy();

    $controller = new class () {
        #[Loggable(message: 'sensitive action', includeRequest: true)]
        public function destroy(): void
        {
        }
    };

    $request = Request::create('http://example.com/items/1', 'DELETE');

    $route = Mockery::mock(Route::class);
    $route->shouldReceive('getAction')->with('uses')->andReturn(get_class($controller) . '@destroy');
    $route->shouldReceive('getController')->andReturn($controller);
    $route->shouldReceive('getActionMethod')->andReturn('destroy');
    $request->setRouteResolver(fn () => $route);

    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    $logger->shouldHaveReceived('log')
        ->once()
        ->withArgs(function ($level, $message, $context) {
            return $level === 'info'
                && $message === 'sensitive action'
                && isset($context['method'], $context['url'], $context['ip'])
                && $context['method'] === 'DELETE';
        });
});

it('passes empty context array when includeRequest is false', function () {
    $logger = makeLoggerSpy();

    $controller = new class () {
        #[Loggable(message: 'quiet action', includeRequest: false)]
        public function show(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'GET', 'show');

    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    $logger->shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message, $context) => $context === []);
});

// ---------------------------------------------------------------------------
// Default message fallback
// ---------------------------------------------------------------------------

it('falls back to "{action} was called" when message is empty', function () {
    $logger = makeLoggerSpy();

    $controller = new class () {
        #[Loggable]
        public function publish(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'POST', 'publish');

    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    $logger->shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $message === 'publish was called');
});

it('uses custom message when explicitly provided', function () {
    $logger = makeLoggerSpy();

    $controller = new class () {
        #[Loggable(message: 'custom explicit message')]
        public function edit(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'GET', 'edit');

    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    $logger->shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $message === 'custom explicit message');
});

// ---------------------------------------------------------------------------
// Null controller guard
// ---------------------------------------------------------------------------

it('returns the response without logging when controller is null', function () {
    $logger = Mockery::spy(LoggerInterface::class);
    Log::shouldReceive('driver')->andReturn($logger);

    $route = Mockery::mock(Route::class);
    $route->shouldReceive('getAction')->with('uses')->andReturn('SomeController@action');
    $route->shouldReceive('getController')->andReturn(null);
    $route->shouldReceive('getActionMethod')->andReturn('action');

    $request = Request::create('/test', 'GET');
    $request->setRouteResolver(fn () => $route);

    $response = (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');
    $logger->shouldNotHaveReceived('log');
});

// ---------------------------------------------------------------------------
// Custom channel
// ---------------------------------------------------------------------------

it('uses Log::channel() when a channel is specified in the attribute', function () {
    $channelLogger = Mockery::mock(LoggerInterface::class);
    $channelLogger->shouldReceive('log')
        ->once()
        ->withArgs(fn ($level, $message) => $level === 'info' && $message === 'channelled log');

    Log::shouldReceive('channel')->with('slack')->andReturn($channelLogger);

    $controller = new class () {
        #[Loggable(message: 'channelled log', channel: 'slack')]
        public function notify(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'POST', 'notify');

    $response = (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});
