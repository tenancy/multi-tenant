<?php

namespace Hyn\Tenancy\Traits;

use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Contracts\Website;

trait ConvertsEntityToWebsite
{
    /**
     * @param $to
     * @return Hostname|Website|null
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
