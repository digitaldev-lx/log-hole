<?php

declare(strict_types=1);

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
        Gate::define('viewLogHole', function ($user = null) {
            /** @var array<int, string> $authorizedUsers */
            $authorizedUsers = config('log-hole.authorized_users');

            if (empty($authorizedUsers)) {
                return true;
            }

            if ($user === null) {
                return false;
            }

            $email = method_exists($user, 'getEmailForVerification')
                ? $user->getEmailForVerification()
                : (string) data_get($user, 'email');

            return in_array($email, $authorizedUsers, strict: true);
        });
    }
}
