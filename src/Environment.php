<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

namespace Hyn\Tenancy;

use Hyn\Tenancy\Contracts\CurrentHostname;
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
            $hostname = $this->dispatch(new HostnameIdentification);

            return $hostname;
        });
    }

    /**
     * @return Models\Customer|null
     */
    public function customer() : ?Models\Customer
    {
        $hostname = $this->hostname();

        return $hostname ? $hostname->customer : null;
    }

    /**
     * @return Models\Hostname|null
     */
    public function hostname() : ?Models\Hostname
    {
        return $this->app->make(CurrentHostname::class);
    }

    /**
     * @return Models\Website|bool
     */
    public function website() : ?Models\Website
    {
        $hostname = $this->hostname();

        return $hostname ? $hostname->website : false;
    }
}
