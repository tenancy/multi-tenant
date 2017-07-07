<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Database\Console\MigrateCommand;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Collection;

class MigrateCommandTest extends Test
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            MigrateCommand::class,
            $this->app->make('tenancy.command.migrate')
        );
    }

    /**
     * @test
     */
    public function runs_on_tenants()
    {
        $code = $this->artisan('tenancy:migrate', [
            '--realpath' => __DIR__ . '/../../migrations',
            '--tenant' => 1,
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, 'Tenant migration didn\'t work out');

        $this->websites->query()->chunk(10, function (Collection $websites) {
            $websites->each(function (Website $website) {
                $this->connection->set($website, $this->connection->migrationName());
                $this->assertTrue($this->connection->migration()->getSchemaBuilder()->hasTable('samples'));
            });
        });
    }

    protected function duringSetUp(Application $app)
    {
        $this->connection = $app->make(Connection::class);
    }
}
