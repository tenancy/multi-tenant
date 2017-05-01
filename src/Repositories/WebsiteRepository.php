<?php

namespace Hyn\Tenancy\Repositories;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository as Contract;
use Hyn\Tenancy\Events\Websites as Events;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Traits\DispatchesEvents;

class WebsiteRepository implements Contract
{
    use DispatchesEvents;
    /**
     * @var Website
     */
    protected $website;

    /**
     * WebsiteRepository constructor.
     * @param Website $website
     */
    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    /**
     * @param string $uuid
     * @return Website|null
     */
    public function findByUuid(string $uuid): ?Website
    {
        return $this->website->newQuery()->where('uuid', $uuid)->first();
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

        $website->save();

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

        $website->save();

        $this->emitEvent(
            new Events\Updated($website)
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

        if ($hard) {
            $website->forceDelete();
        } else {
            $website->delete();
        }

        $this->emitEvent(
            new Events\Deleted($website)
        );

        return $website;
    }
}
