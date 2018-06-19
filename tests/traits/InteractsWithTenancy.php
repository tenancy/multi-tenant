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
use Hyn\Tenancy\Events\Websites\Identified;
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
     * Replicated tenant Website.
     *
     * @var Website
     */
    protected $tenant;

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

    /**
     * Created websites, so we can destroy databases after a test.
     *
     * @var array|Website[]
     */
    protected $tenants = [];

    protected function setUpTenancy()
    {
        $this->websites  = app(WebsiteRepository::class);
        $this->hostnames = app(HostnameRepository::class);

        $this->connection = app(Connection::class);

        $this->connection->system()->beginTransaction();

        $this->handleTenantDestruction();
    }

    protected function handleTenantDestruction()
    {
        Website::created(function (Website $website) {
            $this->tenants[$website->uuid] = $website;
        });
        Website::updating(function (Website $website) {
            if ($website->isDirty('uuid')) {
                $this->tenants[$website->getOriginal('uuid')] = Website::unguarded(function () use ($website) {
                    return new Website($website->getOriginal());
                });
            }
        });
        Website::deleting(function (Website $website) {
            array_forget($this->tenants, $website->uuid);
        });
    }

    protected function loadHostnames()
    {
        $this->hostname = Hostname::where('fqdn', 'local.testing')->firstOrFail();
    }

    protected function getReplicatedWebsite(): Website
    {
        $this->tenant = Website::unguarded(function () {
            return Website::firstOrNew([
                'uuid' => 'tenant.testing'
            ]);
        });

        $this->websites->create($this->tenant);

        return $this->tenant;
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

        if ($save && !$this->hostname->exists) {
            $this->hostnames->create($this->hostname);
        }
    }

    protected function activateTenant()
    {
        $this->rollbackTenant();

        $this->emitEvent(
            new Identified($this->website)
        );

        // Start global tenant transaction.
        $this->connection->get()->beginTransaction();
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

    protected function rollbackTenant()
    {
        if ($this->connection->exists() && $this->connection->get()->transactionLevel() > 0) {
            $this->connection->get()->rollBack();
        }
    }

    protected function cleanupTenancy()
    {
        foreach ([
                     'websites' => ['website', 'tenant'],
                     'hostname'
                 ] as $repository => $set) {
            if (!is_array($set)) {
                $repository = str_plural($set);
                $set = (array)$set;
            }

            collect($set)->each(function (string $property) use ($repository) {
                if (optional($this->{$property})->exists) {
                    $this->{$repository}->delete($this->{$property}, true);
                }
            });
        }

        foreach ($this->tenants as $tenant) {
            $this->websites->delete($tenant, true);
        }

        $this->connection->system()->rollback();
    }
}
