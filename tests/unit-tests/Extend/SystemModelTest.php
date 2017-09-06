<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Tests\Extend\SystemExtend;

class SystemModelTest extends Test
{
    /**
     * @test
     */
    public function uses_correct_connection()
    {
        $model = new SystemExtend();

        $this->assertEquals($model->getConnectionName(), $this->connection->systemName());
        $this->assertEquals($model->getConnection(), $this->connection->system());
    }
}