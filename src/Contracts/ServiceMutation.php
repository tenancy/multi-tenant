<?php

namespace Hyn\Tenancy\Contracts;

use Hyn\Tenancy\Models\Hostname;

interface ServiceMutation
{
    /**
     * Whenever a website is activated, trigger a service update.
     *
     * @param Hostname $hostname
     * @return bool
     */
    public function activate(Hostname $hostname) : bool;
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
     * @param Hostname $from
     * @param Hostname $to
     * @return bool
     */
    public function switch(Hostname $from, Hostname $to) : bool;
}