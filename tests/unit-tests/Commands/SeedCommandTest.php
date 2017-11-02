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

use Hyn\Tenancy\Database\Console\Seeds\SeedCommand;
use Hyn\Tenancy\Models\Website;
use SampleSeeder;

class SeedCommandTest extends DatabaseCommandTest
{
    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            SeedCommand::class,
            $this->app->make(SeedCommand::class)
        );
    }

    /**
     * @test
     */
    public function runs_seed_on_one_tenant()
    {
        /** @var Website $otherWebsite */
        $otherWebsite = $this->website->replicate();
        $this->websites->create($otherWebsite);

        $this->migrateAndTest('migrate');

        $this->assertTrue(
            $this->connection->seed($this->website, SampleSeeder::class),
            "Seeding command failed {$this->website->uuid}."
        );

        $this->connection->set($this->website);

        $this->assertGreaterThan(
            0,
            $this->connection->get()->table('samples')->count(),
            "Unable to seed one single tenant {$this->website->uuid}."
        );

        $this->connection->set($otherWebsite);

        $this->assertEquals(
            0,
            $this->connection->get()->table('samples')->count(),
            "Seeding one tenant also affected another tenant {$otherWebsite->uuid}."
        );
    }

    /**
     * @test
     */
    public function runs_seed_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->seedAndTest(function (Website $website) {
            $this->connection->set($website);

            $this->assertTrue(
                $this->connection->get()->table('samples')->count() === 2,
                "Connection for {$website->uuid} has no sample data seeded"
            );
        });
    }
}
