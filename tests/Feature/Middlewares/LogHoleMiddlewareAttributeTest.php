<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Attributes\Loggable;
use DigitalDevLx\LogHole\Enums\LogLevel;
use DigitalDevLx\LogHole\Middlewares\LogHoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;

// ---------------------------------------------------------------------------
// Named stub controllers required for class-level attribute tests.
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

// ---------------------------------------------------------------------------
// Method-level attribute detection
// ---------------------------------------------------------------------------

it('detects method-level Loggable attribute and writes a log', function () {
    Log::spy();

    $controller = new class () {
        #[Loggable(message: 'order was placed', level: LogLevel::Info)]
        public function store(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'POST', 'store');
    $response = (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $level === 'info' && $message === 'order was placed');
});

it('detects class-level Loggable attribute when the method has no attribute', function () {
    Log::spy();

    $controller = new ClassLevelLoggableController();
    $request = makeRequestWithRoute($controller, 'GET', 'index');

    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $level === 'warning' && $message === 'controller action called');
});

it('method-level attribute takes priority over class-level attribute', function () {
    Log::spy();

    $controller = new ClassAndMethodLoggableController();
    $request = makeRequestWithRoute($controller, 'PUT', 'update');

    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $level === 'error' && $message === 'method-level message');
});

// ---------------------------------------------------------------------------
// includeRequest context
// ---------------------------------------------------------------------------

it('includes method, url, and ip in context when includeRequest is true', function () {
    Log::spy();

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

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(function ($level, $message, $context) {
            return $level === 'info'
                && $message === 'sensitive action'
                && isset($context['method'], $context['url'], $context['ip'])
                && $context['method'] === 'DELETE';
        });
});

it('passes empty context array when includeRequest is false', function () {
    Log::spy();

    $controller = new class () {
        #[Loggable(message: 'quiet action', includeRequest: false)]
        public function show(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'GET', 'show');
    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message, $context) => $context === []);
});

// ---------------------------------------------------------------------------
// Default message fallback
// ---------------------------------------------------------------------------

it('falls back to "{action} was called" when message is empty', function () {
    Log::spy();

    $controller = new class () {
        #[Loggable]
        public function publish(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'POST', 'publish');
    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $message === 'publish was called');
});

it('uses custom message when explicitly provided', function () {
    Log::spy();

    $controller = new class () {
        #[Loggable(message: 'custom explicit message')]
        public function edit(): void
        {
        }
    };

    $request = makeRequestWithRoute($controller, 'GET', 'edit');
    (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message) => $message === 'custom explicit message');
});

// ---------------------------------------------------------------------------
// Null controller guard
// ---------------------------------------------------------------------------

it('returns the response without logging when controller is null', function () {
    Log::spy();

    $route = Mockery::mock(Route::class);
    $route->shouldReceive('getAction')->with('uses')->andReturn('SomeController@action');
    $route->shouldReceive('getController')->andReturnNull();

    $request = Request::create('/test', 'GET');
    $request->setRouteResolver(fn () => $route);

    $response = (new LogHoleMiddleware())->handle($request, fn ($req) => new Response('OK'));

    expect($response->getContent())->toBe('OK');
    Log::shouldNotHaveReceived('log');
});

// ---------------------------------------------------------------------------
// Custom channel
// ---------------------------------------------------------------------------

it('uses Log::channel() when a channel is specified in the attribute', function () {
    $channelLogger = Mockery::mock(Psr\Log\LoggerInterface::class);
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
