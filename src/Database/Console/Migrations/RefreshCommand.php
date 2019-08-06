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

use Hyn\Tenancy\Traits\MutatesMigrationCommands;
use Illuminate\Database\Console\Migrations\RefreshCommand as BaseCommand;

class RefreshCommand extends BaseCommand
{
    use MutatesMigrationCommands;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $database = $this->connection->tenantName();

        $this->input->setOption('force', $force = true);
        $website_id = $this->option('website_id');
        $realpath = $this->option('realpath');
        $path = $this->input->getOption('path');
        $step = $this->input->getOption('step') ?? 0;

        if ($step > 0) {
            $this->call('tenancy:migrate:rollback', [
                '--database' => $database,
                '--realpath' => $realpath,
                '--website_id' => $website_id,
                '--path' => $path,
                '--step' => $step,
                '--force' => $force,
            ]);
        } else {
            $this->call('tenancy:migrate:reset', [
                '--database' => $database,
                '--realpath' => $realpath,
                '--website_id' => $website_id,
                '--path' => $path,
                '--force' => $force,
            ]);
        }

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
