<?php

namespace Hyn\Webserver\Repositories;

use Hyn\Framework\Repositories\BaseRepository;
use Hyn\Webserver\Contracts\SslRepositoryContract;
use Hyn\MultiTenant\Models\Hostname;

class SslRepository extends BaseRepository implements SslRepositoryContract
{
    /**
     * @param Hostname $hostname
     *
     * @return \Hyn\Webserver\Models\SslCertificate
     */
    public function findByHostname(Hostname $hostname)
    {
        return $this->model->with('hostnames')->where(function ($q) use ($hostname) {

        });
    }
}
