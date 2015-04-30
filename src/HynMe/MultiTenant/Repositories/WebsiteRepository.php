<?php namespace HynMe\MultiTenant\Repositories;

use HynMe\Framework\Repositories\BaseRepository;
use HynMe\MultiTenant\Contracts\WebsiteRepositoryContract;
use HynMe\MultiTenant\Models\Hostname;

class WebsiteRepository extends BaseRepository implements WebsiteRepositoryContract
{

    /**
     * @var \HynMe\MultiTenant\Models\Website
     */
    protected $website;

    /**
     * @param Hostname $hostname
     * @return \HynMe\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname)
    {
        return $hostname->website;
    }
}