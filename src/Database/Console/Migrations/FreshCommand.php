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

use Hyn\Tenancy\Contracts\Website;
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

        $this->input->setOption('force', true);
        $this->input->setOption('database', $this->connection->tenantName());

        $this->processHandle(function (Website $website) {
            $this->connection->set($website);

            $this->dropAllTables(
                $database = $this->connection->tenantName()
            );

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

            $this->connection->purge();
        });
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        foreach ($options = parent::getOptions() as $option) {
            if ($option[0] === 'seeder') {
                $option[4] = config('tenancy.db.tenant-seed-class', null);
            }
        }

        return array_merge($options, [
            $this->addWebsiteOption()
        ]);
    }
}
