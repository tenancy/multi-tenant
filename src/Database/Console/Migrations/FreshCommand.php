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

namespace Hyn\Tenancy\Database\Console\Migrations;

use http\Exception\RuntimeException;
use Hyn\Tenancy\Exceptions\ConnectionException;
use Hyn\Tenancy\Traits\MutatesMigrationCommands;
use Illuminate\Database\Console\Migrations\FreshCommand as BaseCommand;

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

        $this->input->setOption('force', $force = true);
        $website_id = $this->option('website_id');
        $realpath = $this->option('realpath');
        $path = $this->input->getOption('path');

        $website = $this->websites->query()->findOrFail($website_id);

        $this->connection->set($website);

        $this->dropAllTables(
            $database = $this->connection->tenantName()
        );

        $this->call('tenancy:migrate', [
            '--database' => $database,
            '--realpath' => $realpath,
            '--website_id' => $website_id,
            '--path' => $path,
            '--force' => $force,
        ]);

        if ($this->needsSeeding()) {
            $this->call('tenancy:db:seed', [
                '--database' => $database,
                '--class' => $this->option('seeder') ?? config('tenancy.db.tenant-seed-class') ?? 'DatabaseSeeder',
                '--force' => $force,
            ]);
        }
    }
}
