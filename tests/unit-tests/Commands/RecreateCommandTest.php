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

use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Foundation\Application;

class RecreateCommandTest extends DatabaseCommandTest
{
    use DispatchesEvents;

    protected function duringSetUp(Application $app)
    {
        $this->cleanupTenancy();

        parent::duringSetUp($app);
    }

    /** @test */
    public function can_recreate_deleted_tenant_database()
    {
        config([
            'tenancy.db.tenant-migrations-path' => __DIR__ . '/../../migrations'
        ]);

        $this->connection->migrate($this->website, __DIR__ . '/../../migrations');

        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('migrations'));

        $this->websites->delete($this->website, true);

        $this->assertFalse($this->website->exists);

        // Save the website instance to the database.
        $this->website->save();

        try {
            if (!$this->assertFalse($this->connection->get()->getSchemaBuilder()->hasTable('migrations'))) {
                $this->fail('`migrations` table in tenant db still exists.');
            }
        } catch (\Exception $e) {
            // Surpress exception
        }

        $this->artisan('tenancy:recreate');

        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('migrations'));
    }
}
