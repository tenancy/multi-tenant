<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Listeners\AffectServicesListener;
use Hyn\Tenancy\Listeners\Models as Listeners;
use Hyn\Tenancy\Listeners\WebsiteUuidGeneration;
use Hyn\Tenancy\Models;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class EventProvider extends EventServiceProvider
{
    /**
     * @var array
     */
    protected $subscribe = [
        // Triggers all services.
        AffectServicesListener::class,
        // Sets the uuid value on a website based on tenancy configuration.
        WebsiteUuidGeneration::class,
    ];

    public function register()
    {
        $this->app->singleton(Connection::class);

        Models\Hostname::observe(Listeners\HostnameObserver::class);
        Models\Customer::observe(Listeners\CustomerObserver::class);
        Models\Website::observe(Listeners\WebsiteObserver::class);

        $this->serviceListeners();
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

    protected function serviceListeners()
    {
        AffectServicesListener::registerService($this->app->make(Connection::class));
    }
}
