<?php

namespace Hyn\Tenancy\Providers;

use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Providers\Tenants\BusProvider;
use Hyn\Tenancy\Providers\Tenants\ConfigurationProvider;
use Hyn\Tenancy\Providers\Tenants\PasswordProvider;
use Hyn\Tenancy\Providers\Tenants\UuidProvider;
use Illuminate\Support\ServiceProvider;

class TenancyProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(ConfigurationProvider::class);
        $this->app->register(PasswordProvider::class);
        $this->app->register(UuidProvider::class);
        $this->app->register(BusProvider::class);
    }

    public function boot()
    {
        // Immediately instantiate the object to work the magic.
        $environment = $this->app->make(Environment::class);
        // Now register it into ioc to make it globally available.
        $this->app->singleton(Environment::class, function () use ($environment) {
            return $environment;
        });
    }
}
