<?php

namespace Hyn\Tenancy\Contracts;

use Hyn\Tenancy\Models\Hostname;

interface ServiceMutation
{
    /**
     * Mutates the service based on a website being enabled.
     *
     * @param Hostname $hostname
     * @return bool
     */
    public function enable(Hostname $hostname) : bool;

    /**
     * Acts on this service whenever a website is disabled.
     *
     * @param Hostname $hostname
     * @return bool
     */
    public function disable(Hostname $hostname) : bool;

    /**
     * Reacts to this service when we switch the active tenant website.
     *
     * @param Hostname $to
     * @param Hostname|null $from
     * @return bool
     */
    public function switch(Hostname $to, Hostname $from = null) : bool;
}
