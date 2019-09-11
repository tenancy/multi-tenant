<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Repositories;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository as Contract;
use Hyn\Tenancy\Events\Hostnames as Events;
use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Hyn\Tenancy\Validators\HostnameValidator;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Database\Eloquent\Builder;

class HostnameRepository implements Contract
{
    use DispatchesEvents;

    /**
     * @var Hostname
     */
    protected $hostname;

    /**
     * @var HostnameValidator
     */
    protected $validator;

    /**
     * @var Factory
     */
    protected $cache;

    /**
     * HostnameRepository constructor.
     * @param Hostname $hostname
     * @param HostnameValidator $validator
     * @param Factory $cache
     */
    public function __construct(Hostname $hostname, HostnameValidator $validator, Factory $cache)
    {
        $this->hostname = $hostname;
        $this->validator = $validator;
        $this->cache = $cache;
    }

    /**
     * @param string $hostname
     * @return Hostname|null
     */
    public function findByHostname(string $hostname)
    {
        $model = $this->cache->remember("tenancy.hostname.$hostname", config('tenancy.hostname.cache'), function () use ($hostname) {
            return $this->hostname->newQuery()->where('fqdn', $hostname)->first() ?? 'none';
        });

        return $model === 'none' ? null : $model;
    }

    /**
     * @return Hostname|null
     */
    public function getDefault()
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

        $this->validator->save($hostname);

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

        $this->validator->save($hostname);

        $dirty = collect(array_keys($hostname->getDirty()))->mapWithKeys(function ($value, $key) use ($hostname) {
            return [ $value => $hostname->getOriginal($value) ];
        });

        $hostname->save();

        $this->cache->forget("tenancy.hostname.{$hostname->fqdn}");

        if ($dirty->has('fqdn')) {
            $this->cache->forget("tenancy.hostname.{$dirty->get('fqdn')}");
        }

        $this->emitEvent(
            new Events\Updated($hostname, $dirty->toArray())
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

        $this->validator->delete($hostname);

        if ($hard) {
            $hostname->forceDelete();
        } else {
            $hostname->delete();
        }

        $this->cache->forget("tenancy.hostname.{$hostname->fqdn}");

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
    public function attach(Hostname &$hostname, Website &$website): Hostname
    {
        $website->hostnames()->save($hostname);

        // Required to refresh relationship objects.
        $website->load('hostnames');

        $this->cache->forget("tenancy.hostname.{$hostname->fqdn}");
        $this->cache->forget("tenancy.website.{$website->uuid}");

        $this->emitEvent(
            new Events\Attached($hostname, $website)
        );

        return $hostname;
    }

    /**
     * @param Hostname $hostname
     * @return Hostname
     */
    public function detach(Hostname &$hostname): Hostname
    {
        $this->cache->forget("tenancy.website.{$hostname->website->uuid}");

        $hostname->website()->dissociate();

        $this->cache->forget("tenancy.hostname.{$hostname->fqdn}");

        $this->emitEvent(
            new Events\Detached($hostname)
        );

        return $hostname;
    }

    /**
     * @warn Only use for querying.
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->hostname->newQuery();
    }
}
