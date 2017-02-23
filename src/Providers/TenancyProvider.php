<?php

namespace Hyn\Tenancy\Providers;

use Hyn\Tenancy\Commands\InstallCommand;
use Hyn\Tenancy\Contracts;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Providers\Tenants as Providers;
use Hyn\Tenancy\Repositories;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class TenancyProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(Providers\ConfigurationProvider::class);
        $this->app->register(Providers\PasswordProvider::class);
        $this->app->register(Providers\UuidProvider::class);
        $this->app->register(Providers\BusProvider::class);
        $this->app->register(Providers\EventProvider::class);
        $this->app->register(Providers\FilesystemProvider::class);

        $this->installCommand();

        $this->repositories();

        $this->migrations();
    }

    public function boot()
    {
        // Immediately instantiate the object to work the magic.
        $environment = $this->app->make(Environment::class);
        // Now register it into ioc to make it globally available.
        $this->app->singleton(Environment::class, function () use ($environment) {
            return $environment;
        });

        Schema::defaultStringLength(191);
    }

    protected function installCommand()
    {
        $this->commands(InstallCommand::class);
    }

    protected function repositories()
    {
        $this->app->singleton(
            Contracts\Repositories\HostnameRepository::class,
            Repositories\HostnameRepository::class
        );
    }

    protected function migrations()
    {
        $this->loadMigrationsFrom(realpath(__DIR__ . '/../../assets/migrations'));
    }

    public function provides()
    {
        return [
            Environment::class,
            InstallCommand::class,
            Contracts\Repositories\HostnameRepository::class,
        ];
    }
}
