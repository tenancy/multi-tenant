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

namespace Hyn\Tenancy\Commands;

use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Website;
use Illuminate\Console\Command;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Database\QueryException;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Hyn\Tenancy\Events\Websites as Events;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class RecreateCommand extends Command
{
    use DispatchesEvents;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:recreate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate all tenant databases present in the system db.';

    /**
     * The name of the migration table.
     *
     * @var string
     */
    protected $table = 'migrations';

    /**
     * Execute the console command.
     */
    public function handle(Connection $connection)
    {
        $this->connection = $connection;

        $websites = Website::all();

        foreach ($websites as $website) {
            if ($this->tenantDatabaseExists($website)) {
                $this->info("Database `{$website->uuid}` exists. Skipping.");
            } else {
                $this->info("Recreating database `{$website->uuid}`.");
                $this->emitEvent(new Events\Created($website));
            }
        }
    }

    /**
     * Checks if tenant database exists.
     *
     * @return bool
     */
    protected function tenantDatabaseExists(Website $website) : bool
    {
        $this->connection->set($website);

        try {
            $schema = $this->connection->get()->getSchemaBuilder();

            if ($schema->hasTable($this->table)) {
                return true;
            }
        } catch (\Exception $e) {
            // Surpress exception
        }

        return false;
    }
}
