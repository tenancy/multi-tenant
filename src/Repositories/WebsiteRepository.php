<?php

namespace Laraflock\MultiTenant\Repositories;

use Hyn\Framework\Repositories\BaseRepository;
use Laraflock\MultiTenant\Contracts\WebsiteRepositoryContract;
use Laraflock\MultiTenant\Models\Hostname;

class WebsiteRepository extends BaseRepository implements WebsiteRepositoryContract
{
    /**
     * @var \Laraflock\MultiTenant\Models\Website
     */
    protected $website;

    /**
     * @var \Laraflock\MultiTenant\Contracts\HostnameRepositoryContract
     */
    protected $hostname;

    /**
     * @param Hostname $hostname
     *
     * @return \Laraflock\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname)
    {
        return $hostname->website;
    }

    /**
     * Return default website.
     *
     * @return \Laraflock\MultiTenant\Models\Website
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
