<?php

namespace DigitalDevLx\LogHole\Commands;

use Illuminate\Console\Command;

class LogHoleCommand extends Command
{
    public $signature = 'log-hole';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
