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
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

namespace Hyn\Tenancy\Tests\Traits;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Traits\DispatchesEvents;

trait InteractsWithTenancy
{
    use DispatchesEvents;
    /**
     * @var Hostname
     */
    protected $hostname;

    /**
     * @var Hostname
     */
    protected $tenant;

    /**
     * @var Website
     */
    protected $website;

    /**
     * @var HostnameRepository
     */
    protected $hostnames;
    /**
     * @var WebsiteRepository
     */
    protected $websites;

    protected function setUpTenancy()
    {
        $this->websites = app(WebsiteRepository::class);
        $this->hostnames = app(HostnameRepository::class);
    }

    protected function loadHostnames()
    {
        $this->hostname = Hostname::where('fqdn', 'local.testing')->firstOrFail();
        $this->tenant = Hostname::where('fqdn', 'tenant.testing')->firstOrFail();
    }

    /**
     * @param bool $save
     */
    protected function setUpHostnames(bool $save = false)
    {
        Hostname::unguard();

        $hostname = new Hostname([
            'fqdn' => 'local.testing',
        ]);

        $this->hostname = $hostname;

        $tenant = new Hostname([
            'fqdn' => 'tenant.testing',
        ]);

        $this->tenant = $tenant;

        Hostname::reguard();

        if ($save) {
            $this->hostnames->create($this->hostname);
            $this->hostnames->create($this->tenant);
        }
    }

    /**
     * @param string $tenant
     */
    protected function activateTenant(string $tenant)
    {
        $hostname = $tenant == 'tenant' ? $this->tenant : $this->hostname;

        $this->emitEvent(
            new Identified($hostname)
        );
    }

    protected function loadWebsites()
    {
        $this->website = Website::firstOrFail();
    }

    /**
     * @param bool $save
     * @param bool $connect
     */
    protected function setUpWebsites(bool $save = false, bool $connect = false)
    {
        $this->website = new Website;

        if ($save) {
            $this->websites->create($this->website);
        }

        if ($connect) {
            $this->website->hostnames()->save($this->hostname);
        }
    }

    protected function cleanupTenancy()
    {
        foreach (['website', 'hostname', 'tenant'] as $property) {
            if ($this->{$property} && $this->{$property}->exists) {
                $this->{$property}->delete();
            }
        }
    }
}
