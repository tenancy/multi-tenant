<?php

namespace Hyn\Tenancy\Repositories;

use Hyn\Framework\Repositories\BaseRepository;
use Hyn\Tenancy\Contracts\HostnameRepositoryContract;
use Illuminate\Support\Facades\Cache;

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
        $value = Cache::remember('hyn_hostname' . $hostname, 10, function () use ($hostname){
            return $this->hostname->where('hostname', $hostname)->first();
        });
        return $value;
    }

    /**
     * @return \Hyn\Tenancy\Models\Hostname
     */
    public function getDefault()
    {
        return env('HYN_MULTI_TENANCY_HOSTNAME') ? $this->hostname->where('hostname', env('HYN_MULTI_TENANCY_HOSTNAME'))->first() : null;
    }

    /**
     * @param int $website_id
     *
     * @return \Hyn\Tenancy\Models\Hostname
     */
    public function findByWebsiteId($website_id)
    {
        $value = Cache::remember('hyn_hostname' . $website_id, 10, function () use ($website_id){
            return $this->hostname->whereHas('website', function ($query) use ($website_id) {
                $query->where('website_id', $website_id);
            })->first();
        });
        return $value;
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
