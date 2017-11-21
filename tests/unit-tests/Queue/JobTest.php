<?php

namespace Hyn\Tenancy\Tests\Queue;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Tests\Extend\JobExtend;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class JobTest extends Test
{
    /**
     * @var Environment
     */
    protected $environment;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->environment = $app->make(Environment::class);
    }

    /**
     * @test
     */
    public function serializes_tenant()
    {
        $this->app->make(CurrentHostname::class);

        $job = new JobExtend();

        $attributes = serialize($job);

        // switch to other
        $this->environment->hostname($this->tenant);

        /** @var CurrentHostname $identified */
        $identified = $this->app->make(CurrentHostname::class);

        /** @var JobExtend $job */
        $job = unserialize($attributes);

        $this->assertInstanceOf(JobExtend::class, $job);

        $restored = $this->environment->hostname();

        $this->assertNotEquals($identified->fqdn, $restored->fqdn);
    }
}
