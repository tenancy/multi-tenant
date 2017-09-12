<?php

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\ResetCommand;
use Hyn\Tenancy\Tests\Test;

class ResetCommandTest extends Test
{
    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            ResetCommand::class,
            $this->app->make(ResetCommand::class)
        );
    }
}