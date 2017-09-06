<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Tests\Extend\TenantExtend;

class TenantModelTest extends Test
{
    /**
     * @test
     */
    public function uses_correct_connection()
    {
        $model = new TenantExtend();

        $this->assertEquals($model->getConnectionName(), $this->connection->tenantName());

        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->activateTenant('local');

        $this->assertEquals($model->getConnection(), $this->connection->get());
    }
}