<?php

namespace Hyn\Tenancy\Tests;

use Illuminate\Contracts\Foundation\Application;

class DatabaseConnectionTest extends Test
{
    /**
     * @test
     */
    public function both_connections_work()
    {

    }

    protected function duringSetUp(Application $app)
    {
        $this->loadHostnames();
    }
}
