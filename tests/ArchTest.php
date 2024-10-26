<?php

use DigitalDevLx\LogHole\Channels\DatabaseChannel;
use Monolog\Handler\AbstractProcessingHandler;

it('DatabaseChannel deve estender AbstractProcessingHandler')
    ->expect(DatabaseChannel::class)
    ->toExtend(AbstractProcessingHandler::class);
