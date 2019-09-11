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

namespace Hyn\Tenancy\Tests\Listeners;

use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class UpdateAppUrlTest extends Test
{
    protected function duringSetUp(Application $app)
    {
        config(['tenancy.hostname.update-app-url' => true]);
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
    }

    /**
     * @test
     */
    public function tenant_url_used()
    {
        $url = config('app.url');
        $this->assertEquals($url, url()->to('/'));

        // seg fault here for me, let's see what CircleCi does.
        $this->activateTenant();

        $this->assertEquals('http://'.$this->hostname->fqdn, config('app.url'));
        $this->assertEquals('http://'.$this->hostname->fqdn, url()->to('/'));
    }
}
