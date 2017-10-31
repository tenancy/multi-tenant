<?php

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
    public function hostname()
    {
        $this->assertEquals($this->hostname->fqdn, Tenancy::hostname()->fqdn);

        Tenancy::hostname($this->tenant);

        $this->assertEquals($this->tenant->fqdn, Tenancy::hostname()->fqdn);
    }

    /**
     * @test
     */
    public function website()
    {
        $this->assertEquals($this->hostname->website_id, Tenancy::website()->id);
    }
}
