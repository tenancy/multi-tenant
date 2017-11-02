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

namespace Hyn\Tenancy\Traits;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Database\Migrations\Migrator;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

trait MutatesMigrationCommands
{
    use AddWebsiteFilterOnCommand;
    /**
     * @var WebsiteRepository
     */
    private $websites;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Migrator $migrator)
    {
        parent::__construct($migrator);

        $this->setName('tenancy:' . $this->getName());
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
        $this->input->setOption('database', $this->connection->tenantName());

        $this->processHandle(function ($website) {
            $this->connection->set($website);

            parent::handle();

            $this->connection->purge();
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

        // Real path option is given.
        if (!is_null($realPath = $this->input->getOption('realpath'))) {
            return [$realPath];
        }

        // Tenant migrations path is configured.
        if ($path = config('tenancy.db.tenant-migrations-path')) {
            return [$path];
        }

        throw new InvalidArgumentException("To prevent unwanted migrations from database/migrations, always specify either path or realpath.");
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge([
            $this->addWebsiteOption(),
            ['realpath', null, InputOption::VALUE_OPTIONAL, 'The absolute path to migration files.', null],
        ], parent::getOptions());
    }
}
