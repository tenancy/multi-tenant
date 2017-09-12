<?php

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\RollbackCommand;
use Hyn\Tenancy\Tests\Test;

class RollbackCommandTest extends Test
{
    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            RollbackCommand::class,
            $this->app->make(RollbackCommand::class)
        );
    }
}