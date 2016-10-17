<?php

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Models\Hostname;

abstract class HostnameEvent extends AbstractEvent
{
    /**
     * @var Hostname
     */
    public $hostname;

    public function __construct(Hostname $hostname)
    {
        $this->hostname = $hostname;
    }
}