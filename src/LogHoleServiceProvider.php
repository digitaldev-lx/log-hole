<?php

namespace DigitalDevLx\LogHole;

use DigitalDevLx\LogHole\Commands\LogHoleCommand;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LogHoleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('log-hole')
            ->hasConfigFile('log-hole')
            ->hasCommand(LogHoleCommand::class)
            ->hasViews('log-hole')
            ->hasMigrations(['create_logs_table']); // Nome da migração sem a extensão ".php"
        // Nome do arquivo de configuração sem extensão
    }

    public function boot()
    {
        parent::boot();

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        Gate::define('view-log-dashboard', function ($user) {
            $authorizedUsers = config('log-hole.authorized_users');

            if (empty($authorizedUsers)) {
                return true;
            }

            return in_array($user->email, $authorizedUsers);
        });

    }
}
