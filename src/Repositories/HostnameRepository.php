<?php namespace LaraLeague\MultiTenant\Repositories;

use HynMe\Framework\Repositories\BaseRepository;
use LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract;

class HostnameRepository extends BaseRepository implements HostnameRepositoryContract
{
    /**
     * @var \LaraLeague\MultiTenant\Models\Hostname
     */
    protected $hostname;

    /**
     * @param $hostname
     * @return \LaraLeague\MultiTenant\Models\Hostname
     */
    public function findByHostname($hostname)
    {
        return $this->hostname->where('hostname', $hostname)->first();
    }

    /**
     * @return \LaraLeague\MultiTenant\Models\Hostname
     */
    public function getDefault()
    {
        return env('HYN_MULTI_TENANCY_HOSTNAME') ? $this->hostname->where('hostname', env('HYN_MULTI_TENANCY_HOSTNAME'))->first() : null;
    }

    /**
     * Create a pagination object
     * @param int $per_page
     * @return mixed
     */
    public function paginated($per_page = 20)
    {
        return $this->hostname->paginate($per_page);
    }
}