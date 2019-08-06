<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Listeners\Database;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events\Websites\Created;
use Hyn\Tenancy\Events\Websites\Updated;
use Illuminate\Contracts\Events\Dispatcher;

class FillsConnectionUsed
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen([Created::class, Updated::class], [$this, 'set']);
    }

    public function set($event)
    {
        if (!$event->website->managed_by_database_connection) {
            $event->website->managed_by_database_connection = $this->connection->systemName();
        }
    }
}
