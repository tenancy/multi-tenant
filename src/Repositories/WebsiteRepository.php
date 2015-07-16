<?php namespace LaraLeague\MultiTenant\Repositories;

use HynMe\Framework\Repositories\BaseRepository;
use LaraLeague\MultiTenant\Contracts\WebsiteRepositoryContract;
use LaraLeague\MultiTenant\Models\Hostname;

class WebsiteRepository extends BaseRepository implements WebsiteRepositoryContract
{

    /**
     * @var \LaraLeague\MultiTenant\Models\Website
     */
    protected $website;

    /**
     * @var \LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract
     */
    protected $hostname;

    /**
     * @param Hostname $hostname
     * @return \LaraLeague\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname)
    {
        return $hostname->website;
    }

    /**
     * Return default website
     *
     * @return \LaraLeague\MultiTenant\Models\Website
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