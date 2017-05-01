<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Listeners\AffectServicesListener;
use Hyn\Tenancy\Listeners\Models as Listeners;
use Hyn\Tenancy\Listeners\WebsiteUuidGeneration;
use Hyn\Tenancy\Models;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class EventProvider extends ServiceProvider
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

    public function boot()
    {
        foreach ($this->subscribe as $listener) {
            $this->app[Dispatcher::class]->subscribe($listener);
        }
    }

    public function register()
    {
        Models\Hostname::observe(Listeners\HostnameObserver::class);
        Models\Customer::observe(Listeners\CustomerObserver::class);
        Models\Website::observe(Listeners\WebsiteObserver::class);
    }
}
