<?php

namespace DigitalDevLx\LogHole;

use DigitalDevLx\LogHole\Commands\LogHoleCommand;
use DigitalDevLx\LogHole\Drivers\Contracts\LogDriverInterface;
use DigitalDevLx\LogHole\Drivers\DriverFactory;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LogHoleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('log-hole')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasCommand(LogHoleCommand::class)
            ->hasMigration('create_logs_table');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(LogDriverInterface::class, function () {
            return DriverFactory::make();
        });
    }

    public function bootingPackage(): void
    {
        Gate::define('viewLogHole', function (?object $user = null) {
            $authorizedUsers = config('log-hole.authorized_users');

            if (empty($authorizedUsers)) {
                return true;
            }

            if ($user === null) {
                return false;
            }

            return in_array($user->email, $authorizedUsers, strict: true);
        });
    }
}
