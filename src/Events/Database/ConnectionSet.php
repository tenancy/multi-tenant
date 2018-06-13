<?php

namespace Hyn\Tenancy\Events\Database;

use Hyn\Tenancy\Abstracts\AbstractEvent;
use Hyn\Tenancy\Models\Website;

class ConnectionSet extends AbstractEvent
{
    /**
     * For which Website the connection was set.
     *
     * @var Website|null
     */
    public $website;
    /**
     * The connection name that was set up, tenant or system.
     *
     * @var string
     */
    public $connection;
    /**
     * The previous connection was disconnected and purged.
     *
     * @var bool
     */
    public $purged;

    public function __construct(Website $website = null, string $connection, bool $purged = true)
    {
        $this->website = $website;
        $this->connection = $connection;
        $this->purged = $purged;
    }
}
