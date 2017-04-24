<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection as DatabaseConnection;

class DatabaseConnectionTest extends Test
{
    use DispatchesEvents;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @test
     */
    public function without_identification_no_tenant_connection_is_active()
    {
        $this->assertNull($this->connection->current());
    }

    /**
     * @test
     * @depends without_identification_no_tenant_connection_is_active
     */
    public function hostname_identification_switches_connection()
    {
        $this->emitEvent(new Identified($this->hostname));
    }

    /**
     * @test
     * @depends hostname_identification_switches_connection
     */
    public function both_connections_work()
    {
        $this->assertTrue($this->connection->get() instanceof DatabaseConnection);
        $this->assertTrue($this->connection->system() instanceof DatabaseConnection);
    }

    protected function duringSetUp(Application $app)
    {
        $this->loadHostnames();

        $this->connection = $app->make(Connection::class);
    }
}
