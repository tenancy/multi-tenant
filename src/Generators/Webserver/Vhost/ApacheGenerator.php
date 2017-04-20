<?php

namespace Hyn\Tenancy\Generators\Webserver\Vhost;

use Hyn\Tenancy\Contracts\Webserver\VhostGenerator;
use Hyn\Tenancy\Models\Website;

class ApacheGenerator implements VhostGenerator
{
    /**
     * @param Website $website
     * @return void
     */
    public function generate(Website $website)
    {
        return view('tenancy.generator::webserver.apache.vhost', compact('website'));
    }
}
