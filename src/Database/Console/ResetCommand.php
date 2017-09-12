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

namespace Hyn\Tenancy\Database\Console;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Database\Console\Migrations\ResetCommand as BaseCommand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

class ResetCommand extends BaseCommand
{
    /**
     * @var WebsiteRepository
     */
    protected $websites;
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Migrator $migrator)
    {
        parent::__construct($migrator);

        $this->setName('tenancy:reset');
        $this->specifyParameters();
        $this->websites = app(WebsiteRepository::class);
        $this->connection = app(Connection::class);
    }

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->input->setOption('force', true);
        $this->input->setOption('database', $this->connection->migrationName());

        $this->websites
            ->query()
            ->chunk(10, function (Collection $websites) {
                $websites->each(function ($website) {
                    $this->connection->set($website, $this->connection->migrationName());

                    parent::handle();
                });
            });
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge([
            ['realpath', null, InputOption::VALUE_OPTIONAL, 'The absolute path to migration files.', null],
        ], parent::getOptions());
    }
}
