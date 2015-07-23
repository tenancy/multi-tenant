<?php namespace Laraflock\MultiTenant\Commands\Migrate;

use App;
use PDOException;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;

class InstallCommand extends \Illuminate\Database\Console\Migrations\InstallCommand
{

    /**
     * Create a new migration install command instance.
     *
     * @param  \Illuminate\Database\Migrations\MigrationRepositoryInterface  $repository
     * @return void
     */
    public function __construct(MigrationRepositoryInterface $repository)
    {
        parent::__construct($repository);

        $this->website = App::make('Laraflock\MultiTenant\Contracts\WebsiteRepositoryContract');
    }

    public function fire()
    {

        if(!$this->option('tenant')) {
            return parent::fire();
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

            $this->repository->setSource($website->database->name);

            try {
                $this->repository->createRepository();
            } catch(PDOException $e)
            {
                if(str_contains($e->getMessage(), ['Base table or view already exists']))
                {
                    $this->info("Migration table already exists: {$e->getMessage()}");
                    continue;
                }
            }

            $this->info('Migration table created successfully.');
        }
    }

    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [['tenant', null, InputOption::VALUE_OPTIONAL, 'The tenant(s) to apply migrations on; use {true|5,8}']]
        );
    }
}