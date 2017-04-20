<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Listeners\AffectServicesListener;
use Hyn\Tenancy\Listeners\Models as Listeners;
use Hyn\Tenancy\Models;
use Illuminate\Support\ServiceProvider;

class EventProvider extends ServiceProvider
{
    protected $subscribe = [
        AffectServicesListener::class
    ];

    public function register()
    {
        $this->app->singleton(Connection::class);

        Models\Hostname::observe(Listeners\HostnameObserver::class);
        Models\Customer::observe(Listeners\CustomerObserver::class);
        Models\Website::observe(Listeners\WebsiteObserver::class);

        $this->serviceListeners();
    }

    public function provides()
    {
        return [
            Connection::class
        ];
    }

    protected function serviceListeners()
    {
        AffectServicesListener::registerService($this->app->make(Connection::class));
    }
}
