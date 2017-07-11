<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Exceptions\ModelValidationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;

class HostnameRepositoryTest extends Test
{
    /**
     * @test
     */
    public function connect_hostname_to_website()
    {
        $this->websites->create($this->website);

        $this->hostnames->attach($this->hostname, $this->website);

        $this->assertEquals($this->website->id, $this->hostname->website_id);
    }

    /**
     * @test
     * @expectedException \Hyn\Tenancy\Exceptions\ModelValidationException
     */
    public function assert_validation_fqdn_required()
    {
        $this->hostname->fqdn = null;

        $this->hostnames->create($this->hostname);
    }

    /**
     * @test
     */
    public function validates_website_relation()
    {
        $this->hostname->website_id = 999;

        try {
            $this->hostnames->create($this->hostname);
        } catch (ModelValidationException $e) {
            $this->assertEquals(
                Arr::get($e->validator->failed(), 'website_id.Exists.0'),
                sprintf("%s.websites", $this->connection->systemName())
            );
        }
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites();
        $this->setUpHostnames();
    }
}
