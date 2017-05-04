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
        // Here, we will check to see if a path option has been defined. If it has we will
        // use the path relative to the root of the installation folder so our database
        // migrations may be run for any customized path from within the application.
        if ($this->input->hasOption('path') && $this->option('path')) {
            return collect($this->option('path'))->map(function ($path) {
                return $this->laravel->basePath().'/'.$path;
            })->all();
        }

        if (! is_null($realPath = $this->input->getOption('realpath'))) {
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
        ], parent::getOptions());
    }
}
