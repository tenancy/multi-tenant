<?php

namespace Hyn\Tenancy\Commands\Migrate;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use PDOException;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends \Illuminate\Database\Console\Migrations\InstallCommand
{
    /**
     * Create a new migration install command instance.
     *
     * @param \Illuminate\Database\Migrations\MigrationRepositoryInterface $repository
     *
     * @return void
     */
    public function __construct(MigrationRepositoryInterface $repository)
    {
        parent::__construct($repository);

        $this->website = app('Hyn\Tenancy\Contracts\WebsiteRepositoryContract');
    }

    public function fire()
    {
        if (! $this->option('tenant')) {
            return parent::fire();
        }

        if ($this->option('tenant') == 'all') {
            $websites = $this->website->all();
        } else {
            $websites = $this->website
                ->queryBuilder()
                ->whereIn('id', explode(',', $this->option('tenant')))
                ->get();
        }

        // forces database to tenant
        if (! $this->option('database')) {
            $this->input->setOption('database', 'tenant');
        }

        foreach ($websites as $website) {
            $this->info("Migrating for {$website->id}: {$website->present()->name}");

            $website->database->setCurrent();

            $this->repository->setSource($website->database->name);

            try {
                $this->repository->createRepository();
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), ['Base table or view already exists'])) {
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
