<?php

namespace DigitalDevLx\LogHole;

use DigitalDevLx\LogHole\Commands\LogHoleCommand;
use Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LogHoleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('log-hole')
            ->hasCommand(LogHoleCommand::class)
            ->hasMigration('create_logs_table');
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'log-hole');
        $this->publishes([
            __DIR__ . '/../config/log-hole.php' => config_path('log-hole.php'),
        ], 'logs-config'); // Tag personalizado

        Gate::define('viewLogHole', function ($user) {

            $authorizedUsers = config('log-hole.authorized_users');
            if (empty($authorizedUsers)) {
                return true;
            }

            return in_array($user->email, config('log-hole.authorized_users'));
        });
    }
}
