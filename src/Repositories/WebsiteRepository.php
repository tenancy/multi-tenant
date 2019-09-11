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

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository as Contract;
use Hyn\Tenancy\Events\Websites as Events;
use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Hyn\Tenancy\Validators\WebsiteValidator;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Database\Eloquent\Builder;

class WebsiteRepository implements Contract
{
    use DispatchesEvents;
    /**
     * @var Website
     */
    protected $website;
    /**
     * @var WebsiteValidator
     */
    protected $validator;
    /**
     * @var Factory
     */
    protected $cache;

    /**
     * WebsiteRepository constructor.
     * @param Website $website
     * @param WebsiteValidator $validator
     * @param Factory $cache
     */
    public function __construct(Website $website, WebsiteValidator $validator, Factory $cache)
    {
        $this->website = $website;
        $this->validator = $validator;
        $this->cache = $cache;
    }

    /**
     * @param string $uuid
     * @return Website|null
     */
    public function findByUuid(string $uuid)
    {
        $model = $this->cache->remember("tenancy.website.$uuid", config('tenancy.website.cache'), function () use ($uuid) {
            return $this->query()->where('uuid', $uuid)->first() ?? 'none';
        });

        return $model === 'none' ? null : $model;
    }

    /**
     * @param string|int $id
     * @return Website|null
     */
    public function findById($id)
    {
        return $this->query()->find($id);
    }

    /**
     * @param Website $website
     * @return Website
     */
    public function create(Website &$website): Website
    {
        if ($website->exists) {
            return $this->update($website);
        }

        $this->emitEvent(
            new Events\Creating($website)
        );

        $this->validator->save($website);

        $website->save();

        $this->cache->forget("tenancy.website.{$website->uuid}");

        $this->emitEvent(
            new Events\Created($website)
        );

        return $website;
    }

    /**
     * @param Website $website
     * @return Website
     */
    public function update(Website &$website): Website
    {
        if (!$website->exists) {
            return $this->create($website);
        }

        $this->emitEvent(
            new Events\Updating($website)
        );

        $this->validator->save($website);

        $dirty = collect(array_keys($website->getDirty()))->mapWithKeys(function ($value, $key) use ($website) {
            return [ $value => $website->getOriginal($value) ];
        });

        $website->save();

        $this->cache->forget("tenancy.website.{$website->uuid}");

        if ($dirty->has('uuid')) {
            $this->cache->forget("tenancy.website.{$dirty->get('uuid')}");
        }

        $this->emitEvent(
            new Events\Updated($website, $dirty->toArray())
        );

        return $website;
    }

    /**
     * @param Website $website
     * @param bool $hard
     * @return Website
     */
    public function delete(Website &$website, $hard = false): Website
    {
        $this->emitEvent(
            new Events\Deleting($website)
        );

        $this->validator->delete($website);

        $hard ? $website->forceDelete() : $website->delete();

        $this->cache->forget("tenancy.website.{$website->uuid}");

        $this->emitEvent(
            new Events\Deleted($website)
        );

        return $website;
    }

    /**
     * @warn Only use for querying.
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->website->newQuery();
    }
}
