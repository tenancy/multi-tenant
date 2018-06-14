<?php

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

        $website = $this->websites->query()->where('id', $website_id)->firstOrFail();
        try
        {
            $this->connection->set($website);
        }
        catch (ConnectionException $e)
        {
            throw new RuntimeException(sprintf('The tenancy website_id=%d does not exist.', $website_id));
        }

        $this->dropAllTables(
            $database = $this->connection->tenantName()
        );

        $this->info('Dropped all tables related to tenant successfully');

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
