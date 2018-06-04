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

namespace Hyn\Tenancy\Tests\Traits;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Database\Connection;
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

    /**
     * @var Connection
     */
    protected $connection;

    protected function setUpTenancy()
    {
        $this->websites = app(WebsiteRepository::class);
        $this->hostnames = app(HostnameRepository::class);

        $this->connection = app(Connection::class);

        // Keeps our database clean.
        config(['auto-delete-tenant-database' => true]);
    }

    protected function loadHostnames()
    {
        $this->hostname = Hostname::where('fqdn', 'local.testing')->firstOrFail();
    }

    protected function getReplicatedHostname(): Hostname
    {
        Hostname::unguard();
        $tenant = Hostname::firstOrNew([
            'fqdn' => 'tenant.testing',
        ]);
        Hostname::reguard();

        return $this->hostnames->create($tenant);
    }

    /**
     * @param bool $save
     */
    protected function setUpHostnames(bool $save = false)
    {
        Hostname::unguard();
        if (!$this->hostname) {
            $hostname = Hostname::firstOrNew([
                'fqdn' => 'local.testing',
            ]);

            $this->hostname = $hostname;
        }
        Hostname::reguard();

        if ($save && ! $this->hostname->exists) {
            $this->hostnames->create($this->hostname);
        }
    }

    protected function activateTenant()
    {
        $this->emitEvent(
            new Identified($this->hostname)
        );
    }

    /**
     * @param bool $save
     * @param bool $connect
     */
    protected function setUpWebsites(bool $save = false, bool $connect = false)
    {
        if (!$this->website) {
            $this->website = new Website;
        }

        if ($save && !$this->website->exists) {
            $this->websites->create($this->website);
        }

        if ($connect && $this->hostname->website_id !== $this->website->id) {
            $this->hostnames->attach($this->hostname, $this->website);
        }
    }

    protected function cleanupTenancy()
    {
        foreach (['hostname', 'website'] as $property) {
            if ($this->{$property} && $this->{$property}->exists) {
                $repo = str_plural($property);
                $this->{$repo}->delete($this->{$property});
            }
        }
    }
}
