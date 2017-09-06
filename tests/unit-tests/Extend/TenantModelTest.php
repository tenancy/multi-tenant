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
    }
}