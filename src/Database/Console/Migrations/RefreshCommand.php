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

use Hyn\Tenancy\Traits\MutatesMigrationCommands;
use Illuminate\Database\Console\Migrations\RefreshCommand as BaseCommand;

class RefreshCommand extends BaseCommand
{
    use MutatesMigrationCommands;

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->input->setOption('force', true);
        $this->input->setOption('database', $this->connection->tenantName());
        $this->input->setOption('path', $this->getRelativeMigrationsPaths());

        $this->processHandle(function ($website) {
            $this->connection->set($website);
            parent::handle();
            $this->connection->purge();
        });
    }

    public function getRelativeMigrationsPaths()
    {
        $paths = $this->getMigrationPaths();
        $relativePaths = [];
        foreach ($paths as $path) {
            $relativePaths[] = str_replace(base_path(), '', $path);
        }

        return $relativePaths;
    }
}
