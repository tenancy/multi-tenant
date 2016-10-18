<?php

namespace Hyn\Tenancy;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Contracts\ServiceMutation;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Jobs\HostnameIdentification;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Hyn\Tenancy\Traits\DispatchesJobs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;

class Environment
{
    use DispatchesJobs, DispatchesEvents;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Collection
     */
    protected $services;

    public function __construct(Application $app)
    {
        $this->app = $app;

        if (config('tenancy.hostname.auto-identification')) {
            $this->identifyHostname();
        }

        $this->services = new Collection();
    }

    /**
     * Auto identification of the tenant hostname to use.
     */
    public function identifyHostname()
    {
        $this->app->singleton(CurrentHostname::class, function () {
            $hostname = $this->dispatch(HostnameIdentification::class);

            if ($hostname) {
                $this->emitEvent(new Identified($hostname));
            }

            return $hostname;
        });
    }

    /**
     * @return Models\Customer|bool
     */
    public function customer()
    {
        /** @var Models\Hostname $hostname */
        $hostname = $this->app->make(CurrentHostname::class);

        return $hostname ? $hostname->customer : false;
    }

    /**
     * @return Models\Hostname|null
     */
    public function hostname()
    {
        return $this->app->make(CurrentHostname::class);
    }

    /**
     * @return Models\Website|bool
     */
    public function website()
    {
        /** @var Models\Hostname $hostname */
        $hostname = $this->app->make(CurrentHostname::class);

        return $hostname ? $hostname->website : false;
    }
}
