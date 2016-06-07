<?php

namespace Hyn\MultiTenant\Contracts;

use Hyn\Framework\Contracts\BaseRepositoryContract;
use Hyn\MultiTenant\Models\Hostname;

interface WebsiteRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param Hostname $hostname
     *
     * @return \Hyn\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname);

    /**
     * Return default website.
     *
     * @return \Hyn\MultiTenant\Models\Website
     */
    public function getDefault();
}
