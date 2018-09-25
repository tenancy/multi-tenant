<?php
/**
 * Created by PhpStorm.
 * User: nitishkumar
 * Date: 3/8/18
 * Time: 9:51 AM
 */

namespace Hyn\Tenancy\Commands;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class RunCommand extends Command
{
    protected $signature = 'tenancy:run {command}
        {--tenant=* : The tenant(s) to apply on}
        {--arguments= : Arguments for the delegated command} 
        {--options= : Options to pass on to the delegated command}
    ';

    protected $description = 'Run another artisan command in a tenant configuration';

    public function handle()
    {
        $websites = $this->getWebsitesFromOption();
        $arguments = $this->option('arguments');
        $options = $this->option('options');
        $this->output->progressStart(count($websites));
        $environment = app(Environment::class);
        foreach ($websites as $website) {
            $environment->tenant($website->find($website->getKey()));
            $this->call($this->argument('command'), array_merge(explode(' ', trim($arguments),explode(' ', trim($options)))));
        }
        $this->output->progressFinish();
    }

    /**
     * @return mixed
     */
    protected function getWebsitesFromOption()
    {
        $repository = app(WebsiteRepository::class);
        if ($this->option('tenant') == 'all') {
            return $repository->query()->all();
        }

        $tenants = explode(',', $this->option('tenant'));

        if (count($tenants) == 0)
        {
            throw new \InvalidArgumentException("The tenant option must be specified!");
        }

        return $repository
            ->query()
            ->whereIn('id', $tenants)
            ->get();
    }
}
