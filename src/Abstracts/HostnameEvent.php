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

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Contracts\Hostname;

abstract class HostnameEvent extends AbstractEvent
{
    /**
     * @var Hostname
     */
    public $hostname;

    public function __construct(Hostname &$hostname = null)
    {
        $this->hostname = &$hostname;
    }
}
