<?php

namespace Hyn\Tenancy\Database\Console;

use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Console\Migrations\MigrateCommand as BaseCommand;

class MigrateCommand extends BaseCommand
{
    /**
     * Create a new migration command instance.
     *
     * @param  \Illuminate\Database\Migrations\Migrator  $migrator
     */
    public function __construct(Migrator $migrator)
    {
        parent::__construct($migrator);
        $this->specifyParameters();
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPaths()
    {
        $paths = parent::getMigrationPaths();

        if (! is_null($realPath = $this->input->getOption('realpath'))) {
            $paths[] = $realPath;
        }

        return $paths;
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
