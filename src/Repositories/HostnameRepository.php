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

namespace Hyn\Tenancy\Repositories;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository as Contract;
use Hyn\Tenancy\Events\Hostnames as Events;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Traits\DispatchesEvents;

class HostnameRepository implements Contract
{
    use DispatchesEvents;
    /**
     * @var Hostname
     */
    protected $hostname;

    /**
     * HostnameRepository constructor.
     * @param Hostname $hostname
     */
    public function __construct(Hostname $hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @param string $hostname
     * @return Hostname|null
     */
    public function findByHostname(string $hostname): ?Hostname
    {
        return $this->hostname->newQuery()->where('fqdn', $hostname)->first();
    }

    /**
     * @return Hostname|null
     */
    public function getDefault() : ?Hostname
    {
        if (config('tenancy.hostname.default')) {
            return $this->hostname->newQuery()->where('fqdn', config('tenancy.hostname.default'))->first();
        }

        return null;
    }

    /**
     * @param Hostname $hostname
     * @return Hostname
     */
    public function create(Hostname &$hostname): Hostname
    {
        if ($hostname->exists) {
            return $this->update($hostname);
        }

        $this->emitEvent(
            new Events\Creating($hostname)
        );

        $hostname->save();

        $this->emitEvent(
            new Events\Created($hostname)
        );

        return $hostname;
    }

    /**
     * @param Hostname $hostname
     * @return Hostname
     */
    public function update(Hostname &$hostname): Hostname
    {
        if (!$hostname->exists) {
            return $this->create($hostname);
        }

        $this->emitEvent(
            new Events\Updating($hostname)
        );

        $dirty = $hostname->getDirty();

        $hostname->save();

        $this->emitEvent(
            new Events\Updated($hostname, $dirty)
        );

        return $hostname;
    }

    /**
     * @param Hostname $hostname
     * @param bool $hard
     * @return Hostname
     */
    public function delete(Hostname &$hostname, $hard = false): Hostname
    {
        $this->emitEvent(
            new Events\Deleting($hostname)
        );

        if ($hard) {
            $hostname->forceDelete();
        } else {
            $hostname->delete();
        }

        $this->emitEvent(
            new Events\Deleted($hostname)
        );

        return $hostname;
    }

    /**
     * @param Hostname $hostname
     * @param Website $website
     * @return Hostname
     */
    public function attach(Hostname $hostname, Website $website): Hostname
    {
        $website->hostnames()->save($hostname);

        $this->emitEvent(
            new Events\Attached($hostname)
        );

        return $hostname;
    }

    /**
     * @param Hostname $hostname
     * @return Hostname
     */
    public function detach(Hostname $hostname): Hostname
    {
        $hostname->website_id = null;

        $this->update($hostname);

        $this->emitEvent(
            new Events\Detached($hostname)
        );

        return $hostname;
    }
}
