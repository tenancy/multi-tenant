<?php

namespace Hyn\MultiTenant\Repositories;

use Hyn\Framework\Repositories\BaseRepository;
use Hyn\MultiTenant\Contracts\WebsiteRepositoryContract;
use Hyn\MultiTenant\Models\Hostname;

class WebsiteRepository extends BaseRepository implements WebsiteRepositoryContract
{
    /**
     * @var \Hyn\MultiTenant\Models\Website
     */
    protected $website;

    /**
     * @var \Hyn\MultiTenant\Contracts\HostnameRepositoryContract
     */
    protected $hostname;

    /**
     * @param Hostname $hostname
     *
     * @return \Hyn\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname)
    {
        return $hostname->website;
    }

    /**
     * Return default website.
     *
     * @return \Hyn\MultiTenant\Models\Website
     */
    public function getDefault()
    {
        return $this->hostname->getDefault()->website;
    }

    /**
     * Create a pagination object.
     *
     * @param int $per_page
     *
     * @return mixed
     */
    public function paginated($per_page = 20)
    {
        return $this->website->paginate($per_page);
    }
}
