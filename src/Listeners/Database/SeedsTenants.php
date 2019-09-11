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
    public function seed(WebsiteEvent $event): bool
    {
        $class = config('tenancy.db.tenant-seed-class');

        if ($class && class_exists($class)) {
            return $this->connection->seed($event->website, $class);
        }

        return true;
    }
}
