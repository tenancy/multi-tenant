<?php

namespace Hyn\Tenancy\Contracts;

use Hyn\Framework\Contracts\BaseRepositoryContract;
use Hyn\Tenancy\Models\Hostname;

interface WebsiteRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param Hostname $hostname
     *
     * @return \Hyn\Tenancy\Models\Website
     */
    public function findByHostname(Hostname $hostname);

    /**
     * Return default website.
     *
     * @return \Hyn\Tenancy\Models\Website
     */
    public function getDefault();
}
