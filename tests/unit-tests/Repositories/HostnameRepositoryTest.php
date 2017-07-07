<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

namespace Hyn\Tenancy\Tests;

use Illuminate\Contracts\Foundation\Application;

class HostnameRepositoryTest extends Test
{
    /**
     * @test
     * @covers \Hyn\Tenancy\Repositories\HostnameRepository::attach
     * @covers \Hyn\Tenancy\Contracts\Repositories\HostnameRepository::attach
     */
    public function connect_hostname_to_website()
    {
        $this->websites->create($this->website);

        $this->hostnames->attach($this->hostname, $this->website);

        $this->assertEquals($this->website->id, $this->hostname->website_id);
    }

    /**
     * @test
     * @covers \Hyn\Tenancy\Validators\HostnameValidator::create
     * @expectedException \Hyn\Tenancy\Exceptions\ModelValidationException
     */
    public function assert_validation_fqdn_required()
    {
        $this->hostname->fqdn = null;

        $this->hostnames->create($this->hostname);
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();
    }
}
