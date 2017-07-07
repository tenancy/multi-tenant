<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

namespace Hyn\Tenancy\Database\Console;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Console\Migrations\MigrateCommand as BaseCommand;

class MigrateCommand extends BaseCommand
{
    protected $name = 'tenancy:migrate';
    /**
     * @var WebsiteRepository
     */
    private $websites;
    /**
     * @var Connection
     */
    private $connection;

    /**
     * Create a new migration command instance.
     *
     * @param  \Illuminate\Database\Migrations\Migrator $migrator
     */
    public function __construct(Migrator $migrator)
    {
        parent::__construct($migrator);

        $this->specifyParameters();
        $this->websites = app(WebsiteRepository::class);
        $this->connection = app(Connection::class);
    }

    public function fire()
    {
        if (!$this->option('tenant')) {
            return parent::fire();
        }

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

                    parent::fire();
                });
            });
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPaths()
    {
        // Here, we will check to see if a path option has been defined. If it has we will
        // use the path relative to the root of the installation folder so our database
        // migrations may be run for any customized path from within the application.
        if ($this->input->hasOption('path') && $this->option('path')) {
            return collect($this->option('path'))->map(function ($path) {
                return $this->laravel->basePath() . '/' . $path;
            })->all();
        }

        if (!is_null($realPath = $this->input->getOption('realpath'))) {
            return [$realPath];
        }

        return array_merge(
            [$this->getMigrationPath()], $this->migrator->paths()
        );
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
            ['tenant', null, InputOption::VALUE_NONE, 'Run migrations for all tenants.'],
        ], parent::getOptions());
    }
}
