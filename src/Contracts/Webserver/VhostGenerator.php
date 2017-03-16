<?php

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
