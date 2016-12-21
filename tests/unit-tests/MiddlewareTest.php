<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Models\Hostname;
use Illuminate\Contracts\Foundation\Application;

class MiddlewareTest extends Test
{
    protected function duringSetUp(Application $app)
    {
        Hostname::unguard();

        $hostname = new Hostname([
            'fqdn' => 'local.testing',
            'redirect_to' => null,
            'force_https' => false,
        ]);

        $app['config']->set('app.url', "http://{$hostname->fqdn}");
    }

    public function redirects_when_specified()
    {
    }
}
