<?php

namespace Laraflock\MultiTenant\Contracts;

use HynMe\Framework\Contracts\BaseRepositoryContract;

interface HostnameRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param $hostname
     *
     * @return \Laraflock\MultiTenant\Models\Hostname
     */
    public function findByHostname($hostname);

    /**
     * @return \Laraflock\MultiTenant\Models\Hostname
     */
    public function getDefault();
}
