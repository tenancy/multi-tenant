<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\Bus\Dispatcher as DispatcherContract;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

class BusProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DispatcherContract::class, function ($app) {
            return new Dispatcher($app);
        });
    }

    public function boot()
    {
        // ..
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            DispatcherContract::class,
        ];
    }
}
