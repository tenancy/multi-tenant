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

    /**
     * @param int $website_id
     *
     * @return \Hyn\Tenancy\Models\Hostname
     */
    public function findByWebsiteId($website_id);
}
