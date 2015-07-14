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
     * @var \HynMe\MultiTenant\Contracts\HostnameRepositoryContract
     */
    protected $hostname;

    /**
     * @param Hostname $hostname
     * @return \HynMe\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname)
    {
        return $hostname->website;
    }

    /**
     * Return default website
     *
     * @return \HynMe\MultiTenant\Models\Website
     */
    public function getDefault()
    {
        return $this->hostname->getDefault()->website;
    }
    /**
     * Create a pagination object
     * @param int $per_page
     * @return mixed
     */
    public function paginated($per_page = 20)
    {
        return $this->website->paginate($per_page);
    }
}