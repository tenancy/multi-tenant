<?php namespace HynMe\MultiTenant\Contracts;

use HynMe\Framework\Contracts\BaseRepositoryContract;
use HynMe\MultiTenant\Models\Hostname;

interface WebsiteRepositoryContract extends BaseRepositoryContract
{

    /**
     * @param Hostname $hostname
     * @return \HynMe\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname);

    /**
     * Return default website
     * @return \HynMe\MultiTenant\Models\Website
     */
    public function getDefault();
}