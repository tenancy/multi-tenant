<?php

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
