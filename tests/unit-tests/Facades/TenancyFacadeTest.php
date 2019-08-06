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

namespace Hyn\Tenancy\Tests\Facades;

use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;
use Hyn\Tenancy\Facades\TenancyFacade as Tenancy;

class TenancyFacadeTest extends Test
{
    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        config(['tenancy.hostname.default' => $this->hostname->fqdn]);
    }

    /**
     * @test
     */
    public function installed()
    {
        $this->assertTrue(Tenancy::installed());
    }

    /**
     * @test
     */
    public function website()
    {
        Tenancy::identifyHostname();

        $this->assertEquals($this->website->uuid, Tenancy::tenant()->uuid);

        $tenant = $this->getReplicatedWebsite();
        Tenancy::tenant($tenant);

        $this->assertEquals($tenant->uuid, Tenancy::tenant()->uuid);
        $this->assertNotEquals($this->website->uuid, Tenancy::tenant()->uuid);
    }

    /**
     * @test
     */
    public function hostname()
    {
        $this->assertEquals($this->hostname->fqdn, Tenancy::hostname()->fqdn);
    }
}
