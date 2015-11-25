<?php

namespace Hyn\MultiTenant\Commands\Seeds;

use App;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

class SeedCommand extends \Illuminate\Database\Console\Seeds\SeedCommand
{
    /**
     * @var \Hyn\MultiTenant\Contracts\WebsiteRepositoryContract
     */
    protected $website;

    public function __construct(Resolver $resolver)
    {
        parent::__construct($resolver);

        $this->website = App::make('Hyn\MultiTenant\Contracts\WebsiteRepositoryContract');
    }

    public function fire()
    {
        if (!$this->confirmToProceed()) {
            return;
        }
        // if no tenant option is set, simply run the native laravel seeder
        if (!$this->option('tenant')) {
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
        if (!$this->option('database')) {
            $this->input->setOption('database', 'tenant');
        }

        foreach ($websites as $website) {
            $this->info("Seeding for {$website->id}: {$website->present()->name}");

            $website->database->setCurrent();

            $this->resolver->setDefaultConnection($website->database->name);

            $this->getSeeder()->run();
        }
    }

    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [['tenant', null, InputOption::VALUE_OPTIONAL, 'The tenant(s) to apply seeds on; use {all|5,8}']]
        );
    }
}
