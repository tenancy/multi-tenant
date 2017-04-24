<?php

namespace Hyn\Tenancy\Repositories;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository as Contract;
use Hyn\Tenancy\Models\Website;

class WebsiteRepository implements Contract
{
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
}
