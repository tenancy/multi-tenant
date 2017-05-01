<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Listeners\AffectServicesListener;
use Illuminate\Support\ServiceProvider;

class ConnectionProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Connection::class);
        AffectServicesListener::registerService($this->app->make(Connection::class));
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            Connection::class
        ];
    }
}
