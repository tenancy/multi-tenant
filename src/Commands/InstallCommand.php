<?php

namespace Hyn\Tenancy\Commands;

use Hyn\Tenancy\Database\Connection;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'tenancy:install';
    protected $description = 'Installs tenancy package.';

    public function handle()
    {
        $code = $this->call('migrate', [
            '--database' => $this->getLaravel()->make(Connection::class)->systemName(),
            '--force' => 1,
            '-n' => 1
        ]);

        if ($code != 0) {
            throw new \RuntimeException("Migrations not run.");
        }
    }
}
