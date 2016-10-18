<?php

namespace Hyn\Tenancy\Contracts\Repositories;

use Hyn\Tenancy\Models\Hostname;

interface HostnameRepository
{
    /**
     * @param string $hostname
     * @return Hostname|null
     */
    public function findByHostname(string $hostname);

    /**
     * @return Hostname|null
     */
    public function getDefault();
}