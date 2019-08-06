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
use Hyn\Tenancy\Contracts\Website;

abstract class WebsiteEvent extends AbstractEvent
{
    /**
     * @var Website
     */
    public $website;

    /**
     * @var Hostname|null
     */
    public $hostname;

    public function __construct(Website &$website, Hostname $hostname = null)
    {
        $this->website = &$website;
        $this->hostname = $hostname;
    }
}
