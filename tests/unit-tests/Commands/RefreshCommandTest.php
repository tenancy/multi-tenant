<?php

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\RefreshCommand;
use Hyn\Tenancy\Tests\Test;

class RefreshCommandTest extends Test
{
    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            RefreshCommand::class,
            $this->app->make(RefreshCommand::class)
        );
    }
}