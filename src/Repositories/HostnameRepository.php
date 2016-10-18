<?php

namespace Hyn\Tenancy\Repositories;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository as Contract;
use Hyn\Tenancy\Models\Hostname;

class HostnameRepository implements Contract
{
    /**
     * @var Hostname
     */
    protected $hostname;

    /**
     * HostnameRepository constructor.
     * @param Hostname $hostname
     */
    public function __construct(Hostname $hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @param string $hostname
     * @return Hostname|null
     */
    public function findByHostname(string $hostname)
    {
        return $this->hostname->newQuery()->where('fqdn', $hostname)->first();
    }

    /**
     * @return Hostname|null
     */
    public function getDefault() : ?Hostname
    {
        if (config('tenancy.hostname.default')) {
            return $this->hostname->newQuery()->where('fqdn', config('tenancy.hostname.default'));
        }
    }
}
