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

namespace Hyn\Tenancy\Tests\Database;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class MultiDatabaseTest extends Test
{
    protected function duringSetUp(Application $app)
    {
        // let's configure secondary database
        $systemName = $app['config']->get('tenancy.db.system-connection-name');
        $config = $app['config']->get("database.connections.$systemName", []);
        $config['host'] = "{$config['host']}2";
        $app['config']->set("database.connections.secondary", $config);

        $this->setUpWebsites();
    }

    /**
     * @test
     */
    public function allow_writing_to_secondary_database()
    {
        if (! env('IN_CI')) {
            return $this->markTestSkipped("Can't access secondary database for testing");
        }

        $this->website->managed_by_database_connection = 'secondary';

        $this->websites->create($this->website);

        $this->assertTrue($this->website->exists);
        $this->assertEquals('secondary', $this->website->managed_by_database_connection);

        // make sure the Website model still uses the regular system name.
        $this->assertEquals(app(Connection::class)->systemName(), $this->website->getConnectionName());

        $this->assertTrue(in_array(
            $this->website->uuid,
            $this->getConnection('secondary')->getDoctrineSchemaManager()->listDatabases()
        ));
    }
}
