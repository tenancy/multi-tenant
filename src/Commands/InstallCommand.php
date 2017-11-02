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

use Carbon\Carbon;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'tenancy:install';
    protected $description = 'Installs tenancy package.';

    public function handle()
    {
        $this->runMigrations();
    }

    protected function runMigrations()
    {
        $code = $this->call('migrate', [
            '--database' => $this->getLaravel()->make(Connection::class)->systemName(),
            '--force' => 1,
            '-n' => 1
        ]);

        if ($code != 0) {
            throw new \RuntimeException("Migrations not run.");
        }

        file_put_contents(base_path('tenancy.json'), json_encode([
            'installed_at' => Carbon::now()->toIso8601String()
        ], JSON_PRETTY_PRINT));
    }
}
