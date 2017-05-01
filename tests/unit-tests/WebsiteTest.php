<?php

namespace Hyn\Tenancy\Tests;

use Illuminate\Contracts\Foundation\Application;

class WebsiteTest extends Test
{

    /**
     * @test
     */
    public function creates_website()
    {
        $this->websites->create($this->website);

        $this->assertTrue($this->website->exists);
    }

    /**
     * @test
     * @depends creates_website
     */
    public function connect_hostname_to_website()
    {
        $this->hostnames->attach($this->hostname, $this->website);

        $this->assertEquals($this->website->id, $this->hostname->website_id);
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();
    }
}
