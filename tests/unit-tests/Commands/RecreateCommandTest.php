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
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;

class RecreateCommandTest extends DatabaseCommandTest
{
    use DispatchesEvents;

    protected function duringSetUp(Application $app)
    {
        foreach($this->websites as $website) {
            $this->websites->delete($website);
        }

        $this->artisan = app(Kernel::class);
        
        parent::duringSetUp($app);
    }

    /** @test */
    public function can_recreate_deleted_tenant_database()
    {
        config([
            'tenancy.db.auto-delete-tenant-database' => true,
            'tenancy.db.tenant-migrations-path' => __DIR__ . '/../../migrations'
        ]);

        $this->connection->migrate($this->website, __DIR__ . '/../../migrations');

        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('migrations'));

        $this->websites->delete($this->website);

        $this->assertFalse($this->website->exists);

        // Save the website instance to the database.
        $this->website->save();

        if (!$this->connection->get()->getSchemaBuilder()->hasTable('migrations')) {
            $this->artisan->call('tenancy:recreate');
        }
        
        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('migrations'));
    }
}
