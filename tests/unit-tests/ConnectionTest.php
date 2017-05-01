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
 *
 */

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Database\Connection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection as DatabaseConnection;

class ConnectionTest extends Test
{

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @test
     */
    public function without_identification_no_tenant_connection_is_active()
    {
        $this->setUpHostnames(true);

        $this->assertNull($this->connection->current(), 'A tenant connection is active, that should not be the case yet.');
    }

    /**
     * @test
     * @depends without_identification_no_tenant_connection_is_active
     */
    public function hostname_identification_switches_connection()
    {
        $this->setUpHostnames(true);
        $this->activateTenant('local');

        $this->assertEquals($this->connection->current(), $this->hostname, 'The tenant hostname is not activated.');

        $failsWithoutWebsite = false;

        try {
            $this->connection->get();
        } catch (\InvalidArgumentException $e) {
            $failsWithoutWebsite = true;
        }

        $this->assertTrue($failsWithoutWebsite, 'Tenant connection should not work, when the hostname has no website.');
        $this->assertTrue($this->connection->system() instanceof DatabaseConnection, 'System connection is not working.');
    }

    /**
     * @test
     * @depends hostname_identification_switches_connection
     */
    public function both_connections_work()
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->activateTenant('local');

        $this->assertTrue($this->connection->get() instanceof DatabaseConnection, 'Tenant connection is not set up properly.');
        $this->assertTrue($this->connection->system() instanceof DatabaseConnection, 'System connection fails once tenant connection is set up.');
    }

    protected function duringSetUp(Application $app)
    {
        $this->connection = $app->make(Connection::class);
    }
}
