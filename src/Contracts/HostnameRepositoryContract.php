<?php

namespace Hyn\MultiTenant\Contracts;

use Hyn\Framework\Contracts\BaseRepositoryContract;

interface HostnameRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param $hostname
     *
     * @return \Hyn\MultiTenant\Models\Hostname
     */
    public function findByHostname($hostname);

    /**
     * @return \Hyn\MultiTenant\Models\Hostname
     */
    public function getDefault();
}
