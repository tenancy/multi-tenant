<?php

namespace Hyn\Webserver\Contracts;

use Hyn\Framework\Contracts\BaseRepositoryContract;
use Hyn\MultiTenant\Models\Hostname;

interface SslRepositoryContract extends BaseRepositoryContract
{
    /**
     * @param Hostname $hostname
     *
     * @return \Hyn\Webserver\Models\SslCertificate
     */
    public function findByHostname(Hostname $hostname);
}
