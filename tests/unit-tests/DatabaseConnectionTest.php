<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Database\Connection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection as DatabaseConnection;

class DatabaseConnectionTest extends Test
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @test
     */
    public function default_connection_works()
    {
        $this->assertNull($this->connection->current());
    }

    public function hostname_identification_switches_connection()
    {
    }

    /**
     * @test
     * @depends default_connection_works
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
