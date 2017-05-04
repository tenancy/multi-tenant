<?php

namespace Hyn\Tenancy\Listeners\Database;

use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Contracts\Events\Dispatcher;
use Hyn\Tenancy\Events;

class ConnectsTenants
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
        $events->listen(Events\Hostnames\Identified::class, [$this, 'switch']);
        $events->listen(Events\Hostnames\Switched::class, [$this, 'switch']);
    }

    /**
     * Reacts to this service when we switch the active tenant website.
     *
     * @param HostnameEvent $event
     * @return bool
     */
    public function switch(HostnameEvent $event) : bool
    {
        return $this->connection->set($event->hostname);
    }
}
