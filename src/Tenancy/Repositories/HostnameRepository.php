<?php

namespace Hyn\Tenancy\Repositories;

use Hyn\Framework\Repositories\BaseRepository;
use Hyn\Tenancy\Contracts\HostnameRepositoryContract;

class HostnameRepository extends BaseRepository implements HostnameRepositoryContract
{
    /**
     * @var \Hyn\Tenancy\Models\Hostname
     */
    protected $hostname;

    /**
     * @param $hostname
     *
     * @return \Hyn\Tenancy\Models\Hostname
     */
    public function findByHostname($hostname)
    {
        return $this->hostname->where('hostname', $hostname)->first();
    }

    /**
     * @return \Hyn\Tenancy\Models\Hostname
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
