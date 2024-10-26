<?php

namespace DigitalDevLx\LogHole;

use DigitalDevLx\LogHole\Commands\LogHoleCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LogHoleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('log-hole')
            ->hasConfigFile('log-hole')
            ->hasCommand(LogHoleCommand::class)
            ->hasMigrations(['create_logs_table']); // Nome da migração sem a extensão ".php"
        // Nome do arquivo de configuração sem extensão
    }
}
