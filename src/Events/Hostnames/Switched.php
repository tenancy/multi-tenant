<?php

namespace Hyn\Tenancy\Events\Websites;

use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Models\Hostname;

class Switched extends HostnameEvent
{
    /**
     * @var Hostname
     */
    public $old;

    /**
     * @param Hostname $hostname
     * @return $this
     */
    public function setOld(Hostname $hostname)
    {
        $this->old = $hostname;

        return $this;
    }
}
