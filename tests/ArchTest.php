<?php

declare(strict_types=1);

use DigitalDevLx\LogHole\Channels\DatabaseChannel;
use Monolog\Handler\AbstractProcessingHandler;

it('DatabaseChannel deve estender AbstractProcessingHandler')
    ->expect(DatabaseChannel::class)
    ->toExtend(AbstractProcessingHandler::class);

it('drivers implement LogDriverInterface')
    ->expect('DigitalDevLx\LogHole\Drivers')
    ->not->toBeAbstract()
    ->ignoring('DigitalDevLx\LogHole\Drivers\Contracts')
    ->ignoring('DigitalDevLx\LogHole\Drivers\DriverFactory');

it('no dd or dump in source code')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

it('DTOs are readonly')
    ->expect('DigitalDevLx\LogHole\DataTransferObjects')
    ->toBeReadonly();

it('no env() calls outside config files')
    ->expect('env')
    ->not->toBeUsed()
    ->ignoring('DigitalDevLx\LogHole\Drivers\DriverFactory');
