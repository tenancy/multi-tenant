<?php

namespace Laraflock\MultiTenant\Contracts;

use Hyn\Framework\Contracts\BaseRepositoryContract;
use Laraflock\MultiTenant\Models\Hostname;

interface WebsiteRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param Hostname $hostname
     *
     * @return \Laraflock\MultiTenant\Models\Website
     */
    public function findByHostname(Hostname $hostname);

    /**
     * Return default website.
     *
     * @return \Laraflock\MultiTenant\Models\Website
     */
    public function getDefault();
}
