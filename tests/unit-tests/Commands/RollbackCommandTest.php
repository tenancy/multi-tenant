<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\Migrations\RollbackCommand;
use Hyn\Tenancy\Models\Website;

class RollbackCommandTest extends DatabaseCommandTest
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

    /**
     * @test
     */
    public function runs_rollback_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->migrateAndTest('migrate:rollback', function (Website $website) {
            $this->connection->set($website);
            $this->assertFalse(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has table samples"
            );
        });
    }
}
