<?php
namespace Hyn\Tenancy\Listeners\Database;

use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Abstracts\WebsiteEvent;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Contracts\Events\Dispatcher;
use Hyn\Tenancy\Events;
class SeedsTenants
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
        $events->listen(Events\Websites\Migrated::class, [$this, 'seed']);
    }

    /**
     *
     * @param WebsiteEvent $event
     * @return bool
     */
    public function seed(WebsiteEvent $event) : bool
    {
        return $this->connection->seed($event->website);
    }
}