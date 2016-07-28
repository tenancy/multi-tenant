<?php

namespace Hyn\Tenancy\Contracts;

use Hyn\Framework\Contracts\BaseRepositoryContract;

interface HostnameRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param $hostname
     *
     * @return \Hyn\Tenancy\Models\Hostname
     */
    public function findByHostname($hostname);

    /**
     * @return \Hyn\Tenancy\Models\Hostname
     */
    public function getDefault();
}
