<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Contracts\Customer;
use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Events\Hostnames\Switched;
use Hyn\Tenancy\Jobs\HostnameIdentification;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Hyn\Tenancy\Traits\DispatchesJobs;
use Illuminate\Contracts\Foundation\Application;

class Environment
{
    use DispatchesJobs, DispatchesEvents;

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        if ($this->installed() && config('tenancy.hostname.auto-identification')) {
            $this->identifyHostname();
            // Identifies the current hostname, sets the binding using the native resolving strategy.
            $this->app->make(CurrentHostname::class);
        }
    }

    /**
     * @return bool
     */
    public function installed(): bool
    {
        return file_exists(base_path('tenancy.json'));
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
     * @return Customer|null
     */
    public function customer()
    {
        $hostname = $this->hostname();

        return $hostname ? $hostname->customer : null;
    }

    /**
     * Get or set the current hostname.
     *
     * @param Hostname|null $model
     * @return Hostname|null
     */
    public function hostname(Hostname $model = null)
    {
        if ($model !== null) {
            $this->app->singleton(CurrentHostname::class, function () use ($model) {
                return $model;
            });

            $this->emitEvent(new Switched($model));

            return $model;
        }

        return $this->app->make(CurrentHostname::class);
    }

    /**
     * @return Website|bool
     */
    public function website()
    {
        $hostname = $this->hostname();

        return $hostname ? $hostname->website : null;
    }
}
