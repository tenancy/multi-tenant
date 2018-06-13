<?php

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\Migrations\FreshCommand;
use Hyn\Tenancy\Models\Website;

class FreshCommandTest extends DatabaseCommandTest
{
    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            FreshCommand::class,
            $this->app->make(FreshCommand::class)
        );
    }

    /**
     * @test
     */
    public function runs_fresh_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->migrateAndTest('migrate:fresh', function (Website $website) {
            $this->connection->set($website);
            $this->assertTrue(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        });
    }
}
