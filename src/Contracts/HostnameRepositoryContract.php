<?php namespace LaraLeague\MultiTenant\Contracts;

use HynMe\Framework\Contracts\BaseRepositoryContract;

interface HostnameRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param $hostname
     * @return \LaraLeague\MultiTenant\Models\Hostname
     */
    public function findByHostname($hostname);

    /**
     * @return \LaraLeague\MultiTenant\Models\Hostname
     */
    public function getDefault();
}