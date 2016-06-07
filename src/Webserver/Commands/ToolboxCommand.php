<?php

namespace Hyn\Webserver\Commands;

use Hyn\Framework\Commands\AbstractCommand;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Hyn\MultiTenant\Contracts\WebsiteRepositoryContract;

class ToolboxCommand extends AbstractCommand
{
    use DispatchesJobs;

    protected $signature = 'webserver:toolbox
        {--update-configs : Update webserver configuration files}';

    protected $description = 'Allows mutation of webserver related to tenancy.';

    /**
     * @var WebsiteRepositoryContract
     */
    protected $website;

    /**
     * @param WebsiteRepositoryContract $website
     */
    public function __construct(WebsiteRepositoryContract $website)
    {
        parent::__construct();

        $this->website = $website;
    }

    /**
     * Handles command execution.
     */
    public function handle()
    {
        $this->website->queryBuilder()->chunk(50, function ($websites) {
            foreach ($websites as $website) {
                if ($this->option('update-configs')) {
                    $this->dispatch(new WebserverCommand($website->id, 'update'));
                } else {
                    $this->error('Unknown option, please specify one.');

                    return;
                }
            }
        });
    }
}
