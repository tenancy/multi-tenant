<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\Migrations\FreshCommand;
use Hyn\Tenancy\Models\Website;
use Illuminate\Contracts\Foundation\Application;
use SampleSeeder;

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
        $this->migrateAndTest('migrate:fresh', function (Website $website) {
            $this->connection->set($website);
            $this->assertTrue(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        });
    }

    /**
     * @test
     */
    public function runs_fresh_with_seeding_on_tenants()
    {
        $this->migrateAndTest('migrate:fresh', function (Website $website) {
            $this->connection->set($website);
            $this->assertTrue(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        }, null, [
            '--seed' => 1,
            '--seeder' => SampleSeeder::class
        ]);
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites(true);
    }
}
