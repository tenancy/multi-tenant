<?php namespace HynMe\MultiTenant\Repositories;

use HynMe\Framework\Repositories\BaseRepository;
use HynMe\MultiTenant\Contracts\HostnameRepositoryContract;

class HostnameRepository extends BaseRepository implements HostnameRepositoryContract
{
    /**
     * @var \HynMe\MultiTenant\Models\Hostname
     */
    protected $hostname;

    /**
     * @param $hostname
     * @return \HynMe\MultiTenant\Models\Hostname
     */
    public function findByHostname($hostname)
    {
        return $this->hostname->where('hostname', $hostname)->first();
    }

    /**
     * @return \HynMe\MultiTenant\Models\Hostname
     */
    public function getDefault()
    {
        return $this->hostname->where('hostname', env('HYN_MULTI_TENANCY_HOSTNAME'))->first();
    }

    /**
     * Creates hostname object for default
     *
     * @throws \Exception
     * @return \HynMe\MultiTenant\Models\Hostname
     */
    public function createDefault()
    {
        $default = env('HYN_MULTI_TENANCY_HOSTNAME');
        if(!$default)
            throw new \Exception("No default hostname to create");

        $model = $this->hostname->create(['hostname' => $default]);

        return $model;
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