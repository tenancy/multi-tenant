<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Events\Hostnames;

use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Contracts\Hostname;

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
