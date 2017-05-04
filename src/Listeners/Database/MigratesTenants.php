<?php

namespace Hyn\Tenancy\Listeners\Database;

use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Contracts\Events\Dispatcher;
use Hyn\Tenancy\Events;

class MigratesTenants
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\Hostnames\Created::class, [$this, 'migrate']);
    }

    /**
     * @param HostnameEvent $event
     * @return bool
     */
    public function migrate(HostnameEvent $event): bool
    {
        if (config('tenancy.db.tenant-migrations-path')) {
            return $this->connection->migrate($event->hostname);
        }

        return true;
    }
}
