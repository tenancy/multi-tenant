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

use Hyn\Tenancy\Traits\DispatchesEvents;
use Hyn\Tenancy\Events\Websites;
use Illuminate\Database\QueryException;
use App\Console\Kernel;

class RecreateCommandTest extends DatabaseCommandTest
{
    use DispatchesEvents;

    /** @test */
    public function can_recreate_deleted_tenant_database()
    {
        config([
            'tenancy.db.auto-delete-tenant-database' => true,
            'tenancy.db.tenant-migrations-path' => __DIR__ . '/../../migrations'
        ]);

        $artisan = app(Kernel::class);

        $this->connection->migrate($this->website, __DIR__ . '/../../migrations');

        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('migrations'));

        // emit delete event to delete database.
        $this->emitEvent(new Websites\Deleted($this->website));

        if (!$this->connection->get()->getSchemaBuilder()->hasTable('migrations')) {
            $artisan->call('tenancy:recreate');
        }
        
        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('migrations'));
    }
}
