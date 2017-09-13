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
        $this->specifyParameters();
        $this->websites = app(WebsiteRepository::class);
        $this->connection = app(Connection::class);
    }

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->input->setOption('force', true);
        $this->input->setOption('database', $this->connection->migrationName());

        $this->websites
            ->query()
            ->chunk(10, function (Collection $websites) {
                $websites->each(function ($website) {
                    $this->connection->set($website, $this->connection->migrationName());

                    parent::handle();
                });
            });
    }
}
