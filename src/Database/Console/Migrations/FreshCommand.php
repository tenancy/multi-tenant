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

namespace Hyn\Tenancy\Database\Console\Migrations;

use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Exceptions\ConnectionException;
use Hyn\Tenancy\Traits\MutatesMigrationCommands;
use Illuminate\Database\Console\Migrations\FreshCommand as BaseCommand;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class FreshCommand extends BaseCommand
{
    use MutatesMigrationCommands;

    /**
     * Execute the console command
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->input->setOption('force', true);
        $this->input->setOption('database', $this->connection->tenantName());

        $this->processHandle(function (Website $website) {
            $database = $this->connection->tenantName();

            $this->wipe($database);

            $this->call('tenancy:migrate', [
                '--database' => $database,
                '--website_id' => [$website->id],
                '--path' => $this->option('path'),
                '--realpath' => $this->option('realpath'),
                '--force' => 1,
            ]);

            if ($this->needsSeeding()) {
                $this->call('tenancy:db:seed', [
                    '--database' => $database,
                    '--website_id' => [$website->id],
                    '--class' => $this->option('seeder'),
                    '--force' => 1,
                ]);
            }
        });
    }

    /**
     * Drops the tenant's tables.
     *
     * db:wipe empties the whole database behind the connection, which belongs
     * to the tenant alone only in the database and schema division modes. In
     * prefix mode it would take the other tenants and the system tables too.
     */
    protected function wipe(string $database)
    {
        $mode = config('tenancy.db.tenant-division-mode');

        if (in_array($mode, [
            Connection::DIVISION_MODE_SEPARATE_DATABASE,
            Connection::DIVISION_MODE_SEPARATE_SCHEMA,
        ], true)) {
            $this->call('db:wipe', array_filter([
                '--database' => $database,
                '--drop-views' => $this->option('drop-views'),
                '--drop-types' => $this->option('drop-types'),
                '--force' => true,
            ]));

            return;
        }

        // Keyed on the mode rather than on the presence of a prefix, since the
        // application may configure one of its own.
        if ($mode !== Connection::DIVISION_MODE_SEPARATE_PREFIX) {
            throw new ConnectionException(
                "Division mode '$mode' marks no tables as the tenant's own, wiping would drop the system tables."
            );
        }

        $connection = $this->connection->get();
        $prefix = $connection->getTablePrefix();

        if ($prefix === '') {
            throw new ConnectionException("Tenant connection carries no table prefix, unable to tell its tables apart.");
        }

        $schema = $connection->getSchemaBuilder();

        $schema->disableForeignKeyConstraints();

        foreach ($this->tenantTables($schema, $prefix) as $table) {
            $schema->drop($table);
        }

        $schema->enableForeignKeyConstraints();
    }

    /**
     * Tenant owned table names, with the prefix stripped off again because the
     * schema builder applies it when dropping.
     */
    protected function tenantTables(SchemaBuilder $schema, string $prefix): array
    {
        // getTableListing() arrived in Laravel 10.37, before which the rows of
        // getAllTables() are shaped by the driver.
        $tables = method_exists($schema, 'getTableListing')
            ? $schema->getTableListing()
            : array_map(function ($table) {
                $table = (array) $table;

                return $table['tablename'] ?? $table['name'] ?? reset($table);
            }, $schema->getAllTables());

        $owned = [];

        foreach ($tables as $table) {
            if (strpos($table, $prefix) === 0) {
                $owned[] = substr($table, strlen($prefix));
            }
        }

        return $owned;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        foreach ($options as &$option) {
            if ($option[0] === 'seeder') {
                $option[4] = config('tenancy.db.tenant-seed-class', null);
            }
        }

        return array_merge($options, [
            $this->addWebsiteOption()
        ]);
    }
}
