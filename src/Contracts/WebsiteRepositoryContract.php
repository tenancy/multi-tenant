<?php namespace LaraLeague\MultiTenant\Contracts;

use HynMe\Framework\Contracts\BaseRepositoryContract;
use LaraLeague\MultiTenant\Models\Hostname;

interface WebsiteRepositoryContract extends BaseRepositoryContract
{

    /**
     * @param Hostname $hostname
     * @return \LaraLeague\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname);

    /**
     * Return default website
     * @return \LaraLeague\MultiTenant\Models\Website
     */
    public function getDefault();
}