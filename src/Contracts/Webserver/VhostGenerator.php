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

namespace Hyn\Tenancy\Contracts\Webserver;

use Hyn\Tenancy\Models\Website;

interface VhostGenerator
{
    /**
     * @param Website $website
     * @return void
     */
    public function generate(Website $website);
}
