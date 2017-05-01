<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

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
