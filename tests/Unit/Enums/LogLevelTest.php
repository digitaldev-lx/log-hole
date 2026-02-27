<?php

use DigitalDevLx\LogHole\Enums\LogLevel;
use Monolog\Level;

it('has all 8 log level cases', function () {
    expect(LogLevel::cases())->toHaveCount(8);
});

it('returns a color string for each case', function (LogLevel $level) {
    expect($level->color())->toBeString()->not->toBeEmpty();
})->with(LogLevel::cases());

it('returns badge classes for each case', function (LogLevel $level) {
    expect($level->badgeClasses())->toBeString()->toContain('bg-');
})->with(LogLevel::cases());

it('converts to Monolog Level', function (LogLevel $level) {
    expect($level->toMonologLevel())->toBeInstanceOf(Level::class);
})->with(LogLevel::cases());

it('converts from Monolog Level', function () {
    expect(LogLevel::fromMonolog(Level::Error))->toBe(LogLevel::Error);
    expect(LogLevel::fromMonolog(Level::Debug))->toBe(LogLevel::Debug);
    expect(LogLevel::fromMonolog(Level::Emergency))->toBe(LogLevel::Emergency);
});

it('converts from string case-insensitive', function () {
    expect(LogLevel::fromString('error'))->toBe(LogLevel::Error);
    expect(LogLevel::fromString('INFO'))->toBe(LogLevel::Info);
    expect(LogLevel::fromString('Warning'))->toBe(LogLevel::Warning);
});

it('has correct string values', function () {
    expect(LogLevel::Emergency->value)->toBe('EMERGENCY');
    expect(LogLevel::Debug->value)->toBe('DEBUG');
    expect(LogLevel::Info->value)->toBe('INFO');
});
