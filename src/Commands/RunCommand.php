<?php
/**
 * Created by PhpStorm.
 * User: nitishkumar
 * Date: 3/8/18
 * Time: 9:51 AM
 */

namespace Hyn\Tenancy\Commands;

use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
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
        $newArgv = ['artisan', $this->argument('command')];
        if ($arguments = $this->option('arguments')) {
            $newArgv = array_merge($newArgv, explode(' ', trim($arguments)));
        }
        if ($options = $this->option('options')) {
            $newArgv = array_merge($newArgv, explode(' ', trim($options)));
        }
        $this->output->progressStart(count($websites));
        foreach ($websites as $website) {
            $tenantApp = require base_path('bootstrap') . '/app.php';
            $kernel = $tenantApp->make(Kernel::class);
            $status = $kernel->handle(
                $input = new ArgvInput($newArgv),
                new ConsoleOutput
            );
            $kernel->terminate($input, $status);
            $this->comment($status);
            $this->output->progressAdvance();
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

        $tenantOptionArr = explode(',', $this->option('tenant'));

        if (count($tenantOptionArr) == 0)
        {
            throw new \InvalidArgumentException("The tenant option must be specified!");
        }

        return $repository
            ->query()
            ->whereIn('id', $tenantOptionArr)
            ->get();
    }
}
