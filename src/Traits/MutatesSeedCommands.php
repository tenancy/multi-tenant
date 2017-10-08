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
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

trait MutatesSeedCommands
{
    /**
     * @var WebsiteRepository
     */
    private $websites;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Resolver $resolver)
    {
        parent::__construct($resolver);

        $this->setName('tenancy:' . $this->getName());
        $this->addOption('--websiteid', null, InputOption::VALUE_OPTIONAL, 'The tenant-id the seed should be run on.');
        $this->specifyParameters();
        $this->websites = app(WebsiteRepository::class);
        $this->connection = app(Connection::class);
    }


    public function handle()
    {
        $websiteId = $this->option('websiteid');
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->input->setOption('force', true);
        $this->input->setOption('database', $this->connection->tenantName());

        if ($websiteId != null) {
            $website = $this->websites->findById($websiteId);
            if ($website == null) {
                throw new \InvalidArgumentException("Website with id {$websiteId} not found.");
            }
            $this->connection->set($website, $this->connection->tenantName());
            parent::handle();
        } else {
            $this->websites
                ->query()
                ->chunk(10, function (Collection $websites) {
                    $websites->each(function ($website) {
                        $this->connection->set($website, $this->connection->tenantName());

                        parent::handle();
                    });
                });
        }
    }
}
