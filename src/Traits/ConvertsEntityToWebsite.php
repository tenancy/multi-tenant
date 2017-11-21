<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Traits;

use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Contracts\Website;

trait ConvertsEntityToWebsite
{
    /**
     * @param $to
     * @return Website|null
     */
    protected function convertWebsiteOrHostnameToWebsite($to)
    {
        $website = null;

        if ($to instanceof Hostname) {
            $website = $to->website;
        }

        if ($to instanceof Website) {
            $website = $to;
        }

        return $website;
    }
}
