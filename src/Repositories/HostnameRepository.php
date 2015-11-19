<?php

namespace Hyn\MultiTenant\Repositories;

use Hyn\Framework\Repositories\BaseRepository;
use Hyn\MultiTenant\Contracts\HostnameRepositoryContract;

class HostnameRepository extends BaseRepository implements HostnameRepositoryContract
{
    /**
     * @var \Hyn\MultiTenant\Models\Hostname
     */
    protected $hostname;

    /**
     * @param $hostname
     *
     * @return \Hyn\MultiTenant\Models\Hostname
     */
    public function findByHostname($hostname)
    {
        return $this->hostname->where('hostname', $hostname)->first();
    }

    /**
     * @return \Hyn\MultiTenant\Models\Hostname
     */
    public function getDefault()
    {
        return env('HYN_MULTI_TENANCY_HOSTNAME') ? $this->hostname->where('hostname', env('HYN_MULTI_TENANCY_HOSTNAME'))->first() : null;
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
        return $this->hostname->paginate($per_page);
    }
}
