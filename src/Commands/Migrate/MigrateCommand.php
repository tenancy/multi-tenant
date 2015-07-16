<?php namespace HynMe\MultiTenant\Commands\Migrate;

use App;
use PDOException;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

class MigrateCommand extends \Illuminate\Database\Console\Migrations\MigrateCommand
{
    /**
     * @var \HynMe\MultiTenant\Contracts\WebsiteRepositoryContract
     */
    protected $website;

    public function __construct(Migrator $migrator)
    {
        parent::__construct($migrator);

        $this->website = App::make('HynMe\MultiTenant\Contracts\WebsiteRepositoryContract');
    }


    public function fire()
    {

        // fallback to default behaviour if we're not talking about multi tenancy
        if(!$this->option('tenant')) {
            return parent::fire();
        }

        if (!$this->confirmToProceed()) {
            return;
        }
        if($this->option('tenant') == 'all') {
            $websites = $this->website->all();
        } else {
            $websites = $this->website
                ->queryBuilder()
                ->whereIn('id', explode(',', $this->option('tenant')))
                ->get();
        }

        // forces database to tenant
        if(!$this->option('database'))
            $this->input->setOption('database', 'tenant');

        foreach($websites as $website)
        {
            $this->info("Migrating for {$website->id}: {$website->present()->name}");

            $website->database->setCurrent();

            $this->prepareDatabase($website->database->name);

            // The pretend option can be used for "simulating" the migration and grabbing
            // the SQL queries that would fire if the migration were to be run against
            // a database for real, which is helpful for double checking migrations.
            $pretend = $this->input->getOption('pretend');

            // Next, we will check to see if a path option has been defined. If it has
            // we will use the path relative to the root of this installation folder
            // so that migrations may be run for any path within the applications.
            if (!is_null($path = $this->input->getOption('path'))) {
                $path = $this->laravel->basePath().'/'.$path;
            } else {
                $path = $this->getMigrationPath();
            }

            try {
                $this->migrator->run($path, $pretend);
            } catch(PDOException $e)
            {
                if(str_contains($e->getMessage(), ['Base table or view already exists']))
                {
                    $this->comment("Migration failed for existing table; probably a system migration: {$e->getMessage()}");
                    continue;
                }
            }

            // Once the migrator has run we will grab the note output and send it out to
            // the console screen, since the migrator itself functions without having
            // any instances of the OutputInterface contract passed into the class.
            foreach ($this->migrator->getNotes() as $note) {
                $this->output->writeln($note);
            }

            // Finally, if the "seed" option has been given, we will re-run the database
            // seed task to re-populate the database, which is convenient when adding
            // a migration and a seed at the same time, as it is only this command.
            if ($this->input->getOption('seed')) {
                $this->call('db:seed', ['--force' => true]);
            }
        }
    }


    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase($connection = null)
    {
        if(!$connection)
            $connection = $this->option('database');

        $this->migrator->setConnection($connection);

        if (!$this->migrator->repositoryExists()) {
            $options = ['--database' => $connection];

            $this->call('migrate:install', $options);
        }
    }

    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [['tenant', null, InputOption::VALUE_OPTIONAL, 'The tenant(s) to apply migrations on; use {all|5,8}']]
        );
    }


}